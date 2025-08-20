<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppCategoryPosition extends Model
{
    protected $fillable = [
        'application_id',
        'country_id',
        'category_id',
        'position',
        'date'
    ];

    protected $casts = [
        'date' => 'date',
        'application_id' => 'integer',
        'country_id' => 'integer',
        'category_id' => 'integer',
        'position' => 'integer'
    ];

    /**
     * Получить позиции приложения по категориям за определенную дату
     */
    public static function getPositionsByDate(int $applicationId, int $countryId, string $date): array
    {
        $positions = self::where([
            'application_id' => $applicationId,
            'country_id' => $countryId,
            'date' => $date
        ])
            ->pluck('position', 'category_id')
            ->toArray();

        return $positions;
    }

    /**
     * Сохранить позиции с заменой существующих
     */
    public static function savePositions(array $positions, int $applicationId, int $countryId, string $date): void
    {
        foreach ($positions as $categoryId => $position) {
            self::updateOrCreate([
                'application_id' => $applicationId,
                'country_id' => $countryId,
                'category_id' => $categoryId,
                'date' => $date
            ], [
                'position' => $position
            ]);
        }
    }
}
