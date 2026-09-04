<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestLoggingMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        // Only log in production or when explicitly enabled
        if (config('app.env') === 'production' || config('logging.log_requests', false)) {
            $this->logRequest($request, $response, $duration);
        }

        return $response;
    }

    private function logRequest(Request $request, Response $response, float $duration): void
    {
        $context = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'user_agent' => substr($request->userAgent() ?? '', 0, 100),
        ];

        // Log slow requests as warning
        if ($duration > 1000) {
            Log::warning('Slow request detected', $context);
        } else {
            Log::info('Request completed', $context);
        }
    }
}
