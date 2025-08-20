<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    protected $fillable = [
        'ip_address',
        'endpoint',
        'parameters',
        'response_status',
        'response_message',
        'response_time_ms',
    ];

    protected $casts = [
        'parameters' => 'array'
    ];

    /**
     * Записать лог запроса
     */
    public static function logRequest(
        string $ipAddress,
        string $endpoint,
        array $parameters,
        int $responseStatus,
        string $responseMessage,
        int $responseTimeMs): void
    {
        self::create([
            'ip_address' => $ipAddress,
            'endpoint' => $endpoint,
            'parameters' => $parameters,
            'response_status' => $responseStatus,
            'response_message' => $responseMessage,
            'response_time_ms' => $responseTimeMs
        ]);
    }
}
