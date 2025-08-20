<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CategoryPositionService;
use Carbon\Carbon;
use Exception;

class FetchCategoryPositions extends Command
{
    protected $signature = 'apptica:fetch-positions {date?}';

    protected $description = 'Загрузить позиции приложения в категориях из Apptica API';

    public function __construct(
        private CategoryPositionService $positionService,
    )
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $applicationId = config('apptica.application_id');
            $countryId = config('apptica.country_id');

            $dateRange = $this->getDateRange();

            $this->info("Загрузка позиций для приложения {$applicationId} в стране {$countryId}");

            if ($dateRange['from'] === $dateRange['to']) {
                $this->info("Дата: {$dateRange['from']}");
            } else {
                $this->info("Период: {$dateRange['from']} - {$dateRange['to']}");
            }

            $allPositions = $this->positionService->processApiDataForMultipleDates(
                $applicationId,
                $countryId,
                $dateRange['from'],
                $dateRange['to']
            );

            $totalSaved = 0;
            foreach ($allPositions as $date => $positions) {
                if (!empty($positions)) {
                    $this->positionService->savePositions($positions, $applicationId, $countryId, $date);
                    $totalSaved += count($positions);
                }
            }

            if ($totalSaved === 0) {
                $this->warn("Данные для указанного периода не найдены");
                return self::FAILURE;
            }

            $this->info("Успешно обработано дат: " . count($allPositions));
            $this->info("Всего загружено позиций: {$totalSaved}");

            return self::SUCCESS;

        } catch (Exception $e) {
            $this->error("Ошибка: " . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Получить диапазон дат для загрузки
     */
    private function getDateRange(): array
    {
        $inputDate = $this->argument('date');

        if ($inputDate) {
            // Если дата указана - загружаем только эту дату
            $date = $this->validateSingleDate($inputDate);
            return [
                'from' => $date,
                'to' => $date
            ];
        }

        // Если дата не указана - загружаем последние 30 дней
        return [
            'from' => now()->subDays(30)->format('Y-m-d'),
            'to' => now()->subDay()->format('Y-m-d')
        ];
    }

    private function validateSingleDate(string $inputDate): string
    {
        $date = Carbon::createFromFormat('Y-m-d', $inputDate);

        if ($date === false || $date->format('Y-m-d') !== $inputDate) {
            throw new Exception("Некорректный формат даты. Используйте YYYY-MM-DD");
        }

        // Проверяем, что дата не в будущем
        if ($date->isFuture()) {
            throw new Exception("Дата не может быть в будущем");
        }

        // Проверяем, что дата не старше 30 дней
        if ($date->diffInDays(now()) > 30) {
            throw new Exception("Дата не может быть старше 30 дней (ограничение Apptica API)");
        }

        return $date->format('Y-m-d');
    }
}
