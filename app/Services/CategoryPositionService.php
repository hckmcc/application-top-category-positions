<?php

namespace App\Services;

use App\Models\AppCategoryPosition;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class CategoryPositionService
{
    public function __construct(
        private AppticaApiService $appticaApi
    ) {}

    /**
     * Обработать данные из API и извлечь позиции по категориям
     */
    public function processApiData(array $apiData, string $targetDate): array
    {
        $categoryPositions = [];

        try {
            foreach ($apiData as $categoryId => $categoryData) {
                if (!is_array($categoryData)) {
                    continue;
                }

                $positions = $this->extractPositionsFromCategory($categoryData, $targetDate);

                if (!empty($positions)) {
                    $bestPosition = min($positions);
                    $categoryPositions[$categoryId] = $bestPosition;
                }
            }

            return $categoryPositions;

        } catch (Exception $e) {
            Log::error("Ошибка при обработке данных API", [
                'target_date' => $targetDate,
                'error' => $e->getMessage()
            ]);

            throw new Exception("Ошибка обработки данных: " . $e->getMessage());
        }
    }

    /**
     * Извлечь позиции из данных категории для определенной даты
     */
    private function extractPositionsFromCategory(array $categoryData, string $targetDate): array
    {
        $positions = [];

        $this->findPositionsRecursively($categoryData, $targetDate, $positions);

        return array_filter($positions, fn($pos) => is_numeric($pos) && $pos > 0);
    }

    /**
     * Рекурсивный поиск позиций в категориях, подкатегориях
     */
    private function findPositionsRecursively(array $data, string $targetDate, array &$positions): void
    {
        foreach ($data as $key => $value) {
            if ($key === $targetDate && is_numeric($value)) {
                $positions[] = (int) $value;
            } elseif (is_array($value)) {
                $this->findPositionsRecursively($value, $targetDate, $positions);
            }
        }
    }

    /**
     * Сохранить позиции в базу данных
     */
    public function savePositions(array $positions, int $applicationId, int $countryId, string $date): void
    {
        try {
            AppCategoryPosition::savePositions($positions, $applicationId, $countryId, $date);
        } catch (Exception $e) {
            Log::error("Ошибка сохранения позиций в БД", [
                'application_id' => $applicationId,
                'country_id' => $countryId,
                'date' => $date,
                'error' => $e->getMessage()
            ]);

            throw new Exception("Ошибка сохранения в БД: " . $e->getMessage());
        }
    }

    /**
     * Получить позиции из базы данных
     */
    public function getPositionsByDate(int $applicationId, int $countryId, string $date): array
    {
        try {
            $positions = AppCategoryPosition::getPositionsByDate($applicationId, $countryId, $date);

            return $positions;

        } catch (Exception $e) {
            Log::error("Ошибка получения позиций из БД", [
                'application_id' => $applicationId,
                'country_id' => $countryId,
                'date' => $date,
                'error' => $e->getMessage()
            ]);

            throw new Exception("Ошибка получения данных из БД: " . $e->getMessage());
        }
    }

    /**
     * Загрузить и обработать данные за определенную дату
     */
    public function fetchAndProcessData(int $applicationId, int $countryId, string $date): array
    {
        $apiData = $this->appticaApi->fetchTopHistory($applicationId, $countryId, $date, $date);

        $positions = $this->processApiData($apiData, $date);

        $this->savePositions($positions, $applicationId, $countryId, $date);

        return $positions;
    }

    public function processApiDataForMultipleDates(int $applicationId, int $countryId, string $dateFrom, string $dateTo): array
    {
        $apiData = $this->appticaApi->fetchTopHistory(
            $applicationId,
            $countryId,
            $dateFrom,
            $dateTo
        );

        $allPositions = [];

        try {
            // Генерируем список всех дат в диапазоне
            $dates = $this->generateDateRange($dateFrom, $dateTo);

            foreach ($apiData as $categoryId => $categoryData) {
                if (!is_array($categoryData)) {
                    continue;
                }

                // Для каждой даты в диапазоне ищем позиции в этой категории
                foreach ($dates as $date) {
                    $positions = $this->extractPositionsFromCategory($categoryData, $date);

                    if (!empty($positions)) {
                        $bestPosition = min($positions);

                        if (!isset($allPositions[$date])) {
                            $allPositions[$date] = [];
                        }

                        $allPositions[$date][$categoryId] = $bestPosition;
                    }
                }
            }

            return $allPositions;

        } catch (Exception $e) {
            Log::error("Ошибка при обработке данных за период", [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'error' => $e->getMessage()
            ]);

            throw new Exception("Ошибка обработки данных за период: " . $e->getMessage());
        }
    }

    /**
     * Сгенерировать список дат в диапазоне
     */
    private function generateDateRange(string $dateFrom, string $dateTo): array
    {
        $dates = [];
        $start = Carbon::createFromFormat('Y-m-d', $dateFrom);
        $end = Carbon::createFromFormat('Y-m-d', $dateTo);

        while ($start->lte($end)) {
            $dates[] = $start->format('Y-m-d');
            $start->addDay();
        }

        return $dates;
    }
}
