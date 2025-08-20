<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetTopCategoriesRequest;
use App\Services\CategoryPositionService;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\Log;

class AppTopCategoryController extends Controller
{
    public function __construct(
        private CategoryPositionService $positionService
    ) {}

    /**
     * Получить позиции приложения в топ чарте по категориям
     */
    public function getTopCategories(GetTopCategoriesRequest $request): JsonResponse
    {
        try {
            $date = $request->getValidatedDate();

            $positions = $this->positionService->getPositionsByDate(
                config('apptica.application_id'),
                config('apptica.country_id'),
                $date
            );

            if (empty($positions)) {
                return $this->errorResponse(
                    "Данные для даты {$date} не найдены",
                    404
                );
            }

            return $this->successResponse($positions);

        } catch (Exception $e) {
            Log::error('Ошибка в AppTopCategoryController', [
                'message' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return $this->errorResponse(
                'Внутренняя ошибка сервера',
                500
            );
        }
    }

    /**
     * Успешный ответ
     */
    private function successResponse(array $data): JsonResponse
    {
        return response()->json([
            'status_code' => 200,
            'message' => 'ok',
            'data' => $data
        ], 200);
    }

    /**
     * Ответ с ошибкой
     */
    private function errorResponse(string $message, int $statusCode, array $details = []): JsonResponse
    {
        $response = [
            'status_code' => $statusCode,
            'message' => $message
        ];

        if (!empty($details)) {
            $response['errors'] = $details;
        }

        return response()->json($response, $statusCode);
    }
}
