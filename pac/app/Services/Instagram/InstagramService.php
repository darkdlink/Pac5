<?php

namespace App\Services\Instagram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Serviço para integração com o Instagram
 *
 * Este serviço permite buscar postagens recentes do Instagram da esteticista
 * para exibir na vitrine interativa da loja.
 */
class InstagramService
{
    protected $accessToken;
    protected $userId;
    protected $apiUrl;
    protected $cacheTime;

    /**
     * Construtor do serviço
     */
    public function __construct()
    {
        $this->accessToken = config('instagram.access_token');
        $this->userId = config('instagram.user_id');
        $this->apiUrl = 'https://graph.instagram.com';
        $this->cacheTime = config('instagram.cache_time', 60); // Default: 60 minutos
    }

    /**
     * Busca as postagens mais recentes do Instagram
     *
     * @param int $limit Número máximo de postagens a serem retornadas
     * @return array
     */
    public function getRecentPosts(int $limit = 9): array
    {
        try {
            // Tenta obter do cache primeiro
            $cacheKey = "instagram_posts_{$this->userId}_{$limit}";

            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            // Se não estiver em cache, consulta a API
            $response = Http::get("{$this->apiUrl}/{$this->userId}/media", [
                'fields' => 'id,caption,media_type,media_url,permalink,thumbnail_url,timestamp,children{media_url,thumbnail_url}',
                'access_token' => $this->accessToken,
                'limit' => $limit
            ]);

            if ($response->successful()) {
                $posts = $response->json()['data'] ?? [];

                // Filtra e processa as postagens
                $processedPosts = $this->processPosts($posts);

                // Armazena em cache
                Cache::put($cacheKey, $processedPosts, now()->addMinutes($this->cacheTime));

                return $processedPosts;
            } else {
                Log::error('Erro ao obter postagens do Instagram', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);

                return [];
            }
        } catch (Exception $e) {
            Log::error('Exceção ao obter postagens do Instagram', [
                'exception' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Processa as postagens obtidas da API
     *
     * @param array $posts Postagens da API do Instagram
     * @return array Postagens processadas
     */
    protected function processPosts(array $posts): array
    {
        $processedPosts = [];

        foreach ($posts as $post) {
            // Tratamento diferente para cada tipo de mídia
            switch ($post['media_type']) {
                case 'IMAGE':
                    $processedPosts[] = [
                        'id' => $post['id'],
                        'type' => 'image',
                        'url' => $post['media_url'],
                        'permalink' => $post['permalink'],
                        'caption' => $post['caption'] ?? '',
                        'timestamp' => $post['timestamp']
                    ];
                    break;

                case 'VIDEO':
                    $processedPosts[] = [
                        'id' => $post['id'],
                        'type' => 'video',
                        'url' => $post['media_url'],
                        'thumbnail' => $post['thumbnail_url'] ?? $post['media_url'],
                        'permalink' => $post['permalink'],
                        'caption' => $post['caption'] ?? '',
                        'timestamp' => $post['timestamp']
                    ];
                    break;

                case 'CAROUSEL_ALBUM':
                    // Para carrosséis, pegamos apenas a primeira imagem
                    $mediaUrl = $post['media_url'];
                    $thumbnailUrl = $post['media_url'];

                    // Se tiver children, pegamos deles
                    if (isset($post['children']['data']) && count($post['children']['data']) > 0) {
                        $firstChild = $post['children']['data'][0];
                        $mediaUrl = $firstChild['media_url'];
                        $thumbnailUrl = $firstChild['thumbnail_url'] ?? $firstChild['media_url'];
                    }

                    $processedPosts[] = [
                        'id' => $post['id'],
                        'type' => 'carousel',
                        'url' => $mediaUrl,
                        'thumbnail' => $thumbnailUrl,
                        'permalink' => $post['permalink'],
                        'caption' => $post['caption'] ?? '',
                        'timestamp' => $post['timestamp'],
                        'has_multiple' => true
                    ];
                    break;
            }
        }

        return $processedPosts;
    }

    /**
     * Limpa o cache de postagens do Instagram
     *
     * @return bool
     */
    public function clearCache(): bool
    {
        try {
            $cachePattern = "instagram_posts_{$this->userId}_*";

            // No Laravel, podemos usar o método forget para excluir uma chave específica
            // Se a implementação do Cache suportar esvaziamento por pattern, use essa abordagem
            // Caso contrário, precisamos excluir explicitamente cada chave conhecida

            // Exemplo simples para os tamanhos de limite mais comuns:
            $limits = [4, 6, 8, 9, 12];

            foreach ($limits as $limit) {
                $cacheKey = "instagram_posts_{$this->userId}_{$limit}";
                Cache::forget($cacheKey);
            }

            return true;
        } catch (Exception $e) {
            Log::error('Erro ao limpar cache de postagens do Instagram', [
                'exception' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Obtém informações sobre a conta conectada
     *
     * @return array
     */
    public function getAccountInfo(): array
    {
        try {
            $cacheKey = "instagram_account_{$this->userId}";

            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $response = Http::get("{$this->apiUrl}/me", [
                'fields' => 'id,username,account_type',
                'access_token' => $this->accessToken
            ]);

            if ($response->successful()) {
                $accountInfo = $response->json();

                // Armazena em cache por mais tempo (1 dia)
                Cache::put($cacheKey, $accountInfo, now()->addDay());

                return $accountInfo;
            } else {
                Log::error('Erro ao obter informações da conta do Instagram', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);

                return [];
            }
        } catch (Exception $e) {
            Log::error('Exceção ao obter informações da conta do Instagram', [
                'exception' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Verifica se o token de acesso é válido
     *
     * @return bool
     */
    public function validateAccessToken(): bool
    {
        try {
            $response = Http::get('https://graph.instagram.com/debug_token', [
                'input_token' => $this->accessToken,
                'access_token' => $this->accessToken
            ]);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                return isset($data['is_valid']) && $data['is_valid'] === true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            Log::error('Exceção ao validar token do Instagram', [
                'exception' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Atualiza o token de acesso (refresh)
     *
     * Nota: Tokens de longa duração precisam ser renovados antes de expirarem (60 dias)
     *
     * @return bool
     */
    public function refreshAccessToken(): bool
    {
        try {
            $response = Http::get('https://graph.instagram.com/refresh_access_token', [
                'grant_type' => 'ig_refresh_token',
                'access_token' => $this->accessToken
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['access_token'])) {
                    // Aqui você deveria salvar o novo token no banco de dados
                    // ou em alguma configuração persistente

                    // Para este exemplo, apenas simulamos o sucesso
                    Log::info('Token do Instagram atualizado com sucesso', [
                        'expires_in' => $data['expires_in'] ?? 'unknown'
                    ]);

                    return true;
                }
            }

            Log::error('Erro ao atualizar token do Instagram', [
                'response' => $response->json()
            ]);

            return false;
        } catch (Exception $e) {
            Log::error('Exceção ao atualizar token do Instagram', [
                'exception' => $e->getMessage()
            ]);

            return false;
        }
    }
}
