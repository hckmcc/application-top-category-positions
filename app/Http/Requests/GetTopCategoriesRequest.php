<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class GetTopCategoriesRequest extends FormRequest
{
    /**
     * Правила валидации запроса.
     */
    public function rules(): array
    {
        return [
            'date' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
                'after:' . now()->subDays(30)->format('Y-m-d')
            ]
        ];
    }

    /**
     * Сообщения для ошибок валидации.
     */
    public function messages(): array
    {
        return [
            'date.required' => 'Параметр date обязателен',
            'date.date_format' => 'Дата должна быть в формате YYYY-MM-DD',
            'date.before_or_equal' => 'Дата не может быть в будущем',
            'date.after' => 'Дата не может быть старше 30 дней'
        ];
    }

    /**
     * Обработка неудачной валидации.
     */
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'status_code' => 422,
            'message' => 'Некорректные параметры запроса',
            'errors' => $validator->errors()->toArray()
        ], 422);

        throw new ValidationException($validator, $response);
    }

    /**
     * Получить валидированную дату
     */
    public function getValidatedDate(): string
    {
        return $this->validated()['date'];
    }
}
