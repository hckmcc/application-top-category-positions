<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    // Ограничения
    private const MAX_REQUESTS = 5;
    private const TIME_WINDOW = 60;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $key = "rate_limit:{$ip}";

        // Получаем текущее количество запросов
        $requests = Cache::get($key, 0);

        // Проверяем лимит
        if ($requests >= self::MAX_REQUESTS) {
            return response()->json([
                'status_code' => 429,
                'message' => 'Превышен лимит запросов. Максимум ' . self::MAX_REQUESTS . ' запросов в минуту'
            ], 429);
        }

        // Увеличиваем счетчик
        if ($requests === 0) {
            Cache::put($key, 1, self::TIME_WINDOW);
        } else {
            Cache::increment($key);
        }

        $response = $next($request);

        $resetTime = time() + self::TIME_WINDOW;

        $response->headers->set('X-RateLimit-Limit', self::MAX_REQUESTS);
        $response->headers->set('X-RateLimit-Remaining', max(0, self::MAX_REQUESTS - $requests - 1));
        $response->headers->set('X-RateLimit-Reset', $resetTime);

        return $response;
    }
}
