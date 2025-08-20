<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AppticaApiService
{
    private int $timeout;

    public function __construct()
    {
        $this->timeout = config('apptica.timeout');
    }

    /**
     * Получить данные истории топа для приложения
     */
    public function fetchTopHistory(int $applicationId, int $countryId, string $dateFrom, string $dateTo): array
    {
        $url = $this->buildUrl($applicationId, $countryId, $dateFrom, $dateTo);

        try {
            $response = Http::timeout($this->timeout)
                ->get($url);

            if (!$response->successful()) {
                throw new Exception("API вернул статус: " . $response->status());
            }

            $data = $response->json();

            if (!isset($data['status_code']) || $data['status_code'] !== 200) {
                throw new Exception("API вернул ошибку: " . ($data['message'] ?? 'Неизвестная ошибка'));
            }

            return $data['data'] ?? [];

        } catch (Exception $e) {
            Log::error("Ошибка при запросе к Apptica API", [
                'url' => $url,
                'error' => $e->getMessage()
            ]);

            throw new Exception("Не удалось получить данные из Apptica API: " . $e->getMessage());
        }
    }

    /**
     * Построить URL для запроса к API
     */
    private function buildUrl(int $applicationId, int $countryId, string $dateFrom, string $dateTo): string
    {
        return sprintf(
            'https://api.apptica.com/package/top_history/%d/%d?date_from=%s&date_to=%s&B4NKGg=fVN5Q9KVOlOHDx9mOsKPAQsFBlEhBOwguLkNEDTZvKzJzT3l',
            $applicationId,
            $countryId,
            $dateFrom,
            $dateTo,
        );
    }
}
