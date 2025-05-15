<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Serviço para processamento de pagamentos via Boleto
 */
class BoletoService implements PaymentGatewayInterface
{
    protected $apiUrl;
    protected $apiKey;
    protected $merchantId;

    /**
     * Construtor do serviço de Boleto
     */
    public function __construct()
    {
        $this->apiUrl = config('payment.boleto.api_url');
        $this->apiKey = config('payment.boleto.api_key');
        $this->merchantId = config('payment.boleto.merchant_id');
    }

    /**
     * Processa um pagamento via boleto
     *
     * @param Order $order Pedido a ser pago
     * @param array $paymentData Dados adicionais para o boleto
     * @return Payment
     * @throws Exception
     */
    public function processPayment(Order $order, array $paymentData): Payment
    {
        try {
            // Valida se tem os dados mínimos necessários para boleto
            if (empty($paymentData['cpf'])) {
                throw new Exception('CPF/CNPJ é obrigatório para pagamento via boleto');
            }

            // Prepara os dados para a API de boleto
            $payload = [
                'transaction_amount' => $order->total,
                'description' => "Pedido #{$order->id} - " . config('app.name'),
                'payment_method_id' => 'bolbradesco', // Exemplo usando Bradesco
                'payer' => [
                    'email' => $order->user->email,
                    'first_name' => $order->user->first_name ?? $paymentData['first_name'] ?? '',
                    'last_name' => $order->user->last_name ?? $paymentData['last_name'] ?? '',
                    'identification' => [
                        'type' => strlen($paymentData['cpf']) > 11 ? 'CNPJ' : 'CPF',
                        'number' => $paymentData['cpf']
                    ],
                    'address' => [
                        'zip_code' => $paymentData['zip_code'] ?? '',
                        'street_name' => $paymentData['street'] ?? '',
                        'street_number' => $paymentData['number'] ?? '',
                        'neighborhood' => $paymentData['neighborhood'] ?? '',
                        'city' => $paymentData['city'] ?? '',
                        'federal_unit' => $paymentData['state'] ?? ''
                    ]
                ],
                'notification_url' => route('webhooks.payment', ['provider' => 'boleto']),
                'external_reference' => (string) $order->id,
                'date_of_expiration' => now()->addDays(3)->format('Y-m-d\TH:i:s.000P')
            ];

            // Faz a requisição para a API de pagamento
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json'
            ])->post("{$this->apiUrl}/payments", $payload);

            if ($response->successful()) {
                $responseData = $response->json();

                // Cria o registro de pagamento
                $payment = new Payment();
                $payment->order_id = $order->id;
                $payment->amount = $order->total;
                $payment->gateway = 'boleto';
                $payment->gateway_payment_id = $responseData['id'];
                $payment->status = 'pending';
                $payment->boleto_url = $responseData['transaction_details']['external_resource_url'] ?? null;
                $payment->boleto_barcode = $responseData['barcode'] ?? null;
                $payment->expiration_date = now()->addDays(3);
                $payment->payment_data = json_encode($responseData);
                $payment->save();

                return $payment;
            } else {
                $errorResponse = $response->json();
                Log::error('Erro ao processar pagamento via boleto', [
                    'order_id' => $order->id,
                    'error' => $errorResponse
                ]);

                throw new Exception("Erro ao processar pagamento: " .
                    ($errorResponse['message'] ?? 'Falha na comunicação com o gateway de pagamento'));
            }
        } catch (Exception $e) {
            Log::error('Exceção ao processar pagamento via boleto', [
                'order_id' => $order->id,
                'exception' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Reembolsa um pagamento de boleto
     *
     * Obs: Normalmente boletos não são reembolsados pelo gateway, mas por transferência bancária
     * direta para o cliente. Este método serve para registrar o reembolso no sistema.
     *
     * @param Payment $payment Pagamento a ser reembolsado
     * @param float|null $amount Valor a ser reembolsado
     * @return bool
     */
    public function refundPayment(Payment $payment, ?float $amount = null): bool
    {
        try {
            // Se o boleto ainda não foi pago, podemos cancelá-lo
            if ($payment->status === 'pending') {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json'
                ])->put("{$this->apiUrl}/payments/{$payment->gateway_payment_id}", [
                    'status' => 'cancelled'
                ]);

                if ($response->successful()) {
                    $payment->status = 'cancelled';
                    $payment->save();

                    return true;
                } else {
                    Log::error('Erro ao cancelar boleto', [
                        'payment_id' => $payment->id,
                        'error' => $response->json()
                    ]);

                    return false;
                }
            }
            // Se o boleto já foi pago, registramos o reembolso no sistema
            else if ($payment->status === 'approved' || $payment->status === 'paid') {
                $refundAmount = $amount ?? $payment->amount;

                // Registramos um reembolso manual
                $payment->status = 'refunded';
                $payment->refunded_at = now();
                $payment->refunded_amount = $refundAmount;
                $payment->save();

                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            Log::error('Exceção ao reembolsar/cancelar boleto', [
                'payment_id' => $payment->id,
                'exception' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Verifica o status atual de um pagamento
     *
     * @param Payment $payment Pagamento a ser verificado
     * @return string Status atual do pagamento
     */
    public function checkPaymentStatus(Payment $payment): string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json'
            ])->get("{$this->apiUrl}/payments/{$payment->gateway_payment_id}");

            if ($response->successful()) {
                $paymentData = $response->json();
                $newStatus = $this->mapStatusFromProvider($paymentData['status']);

                // Atualiza o status do pagamento se mudou
                if ($payment->status !== $newStatus) {
                    $payment->status = $newStatus;
                    $payment->save();

                    // Se o pagamento foi aprovado, atualiza o pedido
                    if ($newStatus === 'approved' && $payment->order->status !== 'paid') {
                        $order = $payment->order;
                        $order->status = 'paid';
                        $order->paid_at = now();
                        $order->save();

                        // Dispara evento de pagamento recebido
                        event(new \App\Events\PaymentReceived($payment));
                    }
                }

                return $newStatus;
            } else {
                Log::error('Erro ao verificar status do boleto', [
                    'payment_id' => $payment->id,
                    'error' => $response->json()
                ]);

                return $payment->status;
            }
        } catch (Exception $e) {
            Log::error('Exceção ao verificar status do boleto', [
                'payment_id' => $payment->id,
                'exception' => $e->getMessage()
            ]);

            return $payment->status;
        }
    }

    /**
     * Gera um link de pagamento para boleto
     *
     * @param Order $order Pedido a ser pago
     * @param array $paymentData Dados adicionais de pagamento
     * @return string URL do boleto
     */
    public function generatePaymentLink(Order $order, array $paymentData): string
    {
        try {
            $payment = $this->processPayment($order, $paymentData);
            return $payment->boleto_url ?? '';
        } catch (Exception $e) {
            Log::error('Erro ao gerar link de boleto', [
                'order_id' => $order->id,
                'exception' => $e->getMessage()
            ]);

            return '';
        }
    }

    /**
     * Processa notificações de webhook do gateway
     *
     * @param array $payload Dados recebidos no webhook
     * @return bool
     */
    public function handleWebhook(array $payload): bool
    {
        try {
            // Verifica a autenticidade do webhook
            if (!$this->validateWebhookSignature($payload)) {
                Log::warning('Assinatura de webhook de boleto inválida', [
                    'payload' => $payload
                ]);
                return false;
            }

            $paymentId = $payload['data']['id'] ?? null;
            if (!$paymentId) {
                Log::error('ID de pagamento não encontrado no webhook de boleto', [
                    'payload' => $payload
                ]);
                return false;
            }

            // Busca o pagamento no banco de dados
            $payment = Payment::where('gateway', 'boleto')
                ->where('gateway_payment_id', $paymentId)
                ->first();

            if (!$payment) {
                Log::error('Pagamento não encontrado para webhook de boleto', [
                    'gateway_payment_id' => $paymentId
                ]);
                return false;
            }

            // Atualiza o status do pagamento consultando a API diretamente
            // ao invés de confiar apenas na notificação do webhook
            $this->checkPaymentStatus($payment);

            return true;
        } catch (Exception $e) {
            Log::error('Exceção ao processar webhook de boleto', [
                'payload' => $payload,
                'exception' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Valida a assinatura do webhook
     *
     * @param array $payload Dados do webhook
     * @return bool
     */
    protected function validateWebhookSignature(array $payload): bool
    {
        // Implementação da validação de assinatura do webhook
        // A validação exata depende do gateway usado

        // Para fins de exemplo, assumimos que a validação é feita pelo ID do comerciante
        return isset($payload['merchant_id']) && $payload['merchant_id'] === $this->merchantId;
    }

    /**
     * Mapeia o status do provedor para o formato interno
     *
     * @param string $providerStatus Status do provedor de pagamento
     * @return string Status interno
     */
    protected function mapStatusFromProvider(string $providerStatus): string
    {
        $statusMap = [
            'pending' => 'pending',
            'approved' => 'approved',
            'in_process' => 'processing',
            'rejected' => 'failed',
            'cancelled' => 'cancelled',
            'refunded' => 'refunded'
        ];

        return $statusMap[$providerStatus] ?? 'pending';
    }
}
