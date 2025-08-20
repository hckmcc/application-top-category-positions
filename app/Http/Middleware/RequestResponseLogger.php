<?php

namespace App\Http\Middleware;

use App\Models\RequestLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestResponseLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $responseTime = round((microtime(true) - $startTime) * 1000);

        $this->logRequestResponse($request, $response, $responseTime);

        return $response;
    }

    /**
     * Логирование запроса и ответа
     */
    private function logRequestResponse(Request $request, Response $response, int $responseTime): void
    {
        try {
            $responseStatus = $response->getStatusCode();
            $responseMessage = 'Unknown';

            if ($response->headers->get('Content-Type') === 'application/json' ||
                str_contains($response->headers->get('Content-Type', ''), 'application/json')) {

                $content = $response->getContent();
                if ($content) {
                    $responseData = json_decode($content, true) ?? [];
                    $responseMessage = $responseData['message'] ?? 'No message';
                }
            }

            $parameters = [
                'query' => $request->query(),
                'user_agent' => $request->userAgent(),
                'method' => $request->method()
            ];

            RequestLog::logRequest(
                $request->ip(),
                $request->getPathInfo(),
                $parameters,
                $responseStatus,
                $responseMessage,
                $responseTime,
            );

        } catch (\Exception $e) {
            Log::warning('Не удалось записать лог запроса', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
