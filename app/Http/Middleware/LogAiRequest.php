<?php

namespace App\Http\Middleware;

use App\Models\AiAuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAiRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);
        $response = $next($request);
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

        try {
            $token = $request->attributes->get('ai_token');

            AiAuditLog::create([
                'ai_api_token_id' => $token?->id,
                'user_id' => $token?->user_id,
                'channel' => 'ai',
                'action' => (string) ($request->route()?->getName() ?: 'ai.request'),
                'http_method' => $request->method(),
                'route' => $request->route()?->uri(),
                'url' => mb_substr((string) $request->fullUrl(), 0, 2048),
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 2000),
                'status_code' => $response->getStatusCode(),
                'duration_ms' => $durationMs,
                'request_query' => $request->query(),
                'meta' => [
                    'request_id' => $request->header('X-Request-Id'),
                ],
            ]);
        } catch (\Throwable $ignored) {
            // Never fail API flow because of audit log errors.
        }

        return $response;
    }
}

