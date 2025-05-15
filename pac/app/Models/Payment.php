<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'payment_method', // 'credit_card', 'pix', 'boleto'
        'status', // 'pending', 'approved', 'failed', 'refunded'
        'amount',
        'transaction_id',
        'gateway', // 'mercadopago', 'pagseguro', etc.
        'gateway_response', // JSON response from payment gateway
        'payer_name',
        'payer_email',
        'payer_document',
        'card_last_four',
        'card_brand',
        'expiration_date', // For boleto
        'pix_qr_code',
        'pix_expiration',
        'boleto_url',
        'paid_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'expiration_date' => 'datetime',
        'pix_expiration' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'gateway_response',
    ];

    /**
     * Get the order associated with the payment.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if payment is approved.
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if payment is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment failed.
     */
    public function isFailed()
    {
        return $this->status === 'failed';
    }

    /**
     * Check if payment is refunded.
     */
    public function isRefunded()
    {
        return $this->status === 'refunded';
    }

    /**
     * Check if payment is via credit card.
     */
    public function isCreditCard()
    {
        return $this->payment_method === 'credit_card';
    }

    /**
     * Check if payment is via PIX.
     */
    public function isPix()
    {
        return $this->payment_method === 'pix';
    }

    /**
     * Check if payment is via boleto.
     */
    public function isBoleto()
    {
        return $this->payment_method === 'boleto';
    }

    /**
     * Mark payment as approved.
     */
    public function markAsApproved($transactionId = null)
    {
        $this->status = 'approved';
        $this->paid_at = now();

        if ($transactionId) {
            $this->transaction_id = $transactionId;
        }

        $this->save();

        // Update order payment status
        $this->order->markAsPaid();

        // Event could be triggered here
        // event(new PaymentApproved($this));

        return $this;
    }

    /**
     * Mark payment as failed.
     */
    public function markAsFailed($gatewayResponse = null)
    {
        $this->status = 'failed';

        if ($gatewayResponse) {
            $this->gateway_response = array_merge(
                $this->gateway_response ?? [],
                ['error_response' => $gatewayResponse]
            );
        }

        $this->save();

        // Event could be triggered here
        // event(new PaymentFailed($this));

        return $this;
    }

    /**
     * Process a refund.
     */
    public function refund($amount = null, $reason = null)
    {
        $this->status = 'refunded';

        // Store refund details in gateway_response
        $refundData = [
            'refunded_at' => now()->toIso8601String(),
            'refund_amount' => $amount ?? $this->amount,
            'refund_reason' => $reason,
        ];

        $this->gateway_response = array_merge(
            $this->gateway_response ?? [],
            ['refund_data' => $refundData]
        );

        $this->save();

        // Event could be triggered here
        // event(new PaymentRefunded($this));

        return $this;
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute()
    {
        return 'R$ ' . number_format($this->amount, 2, ',', '.');
    }

    /**
     * Get payment status label.
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pendente',
            'approved' => 'Aprovado',
            'failed' => 'Falhou',
            'refunded' => 'Reembolsado',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get payment method label.
     */
    public function getMethodLabelAttribute()
    {
        $labels = [
            'credit_card' => 'Cartão de Crédito',
            'pix' => 'PIX',
            'boleto' => 'Boleto Bancário',
        ];

        return $labels[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Scope payments by status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope payments by method.
     */
    public function scopeWithMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope payments created within a date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
