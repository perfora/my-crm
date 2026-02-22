<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Models\SystemLog;
use App\Support\LogSanitizer;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'ai.auth' => \App\Http\Middleware\AuthenticateAiToken::class,
            'ai.scope' => \App\Http\Middleware\EnsureAiScope::class,
            'ai.audit' => \App\Http\Middleware\LogAiRequest::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Throwable $e): void {
            if ($e instanceof NotFoundHttpException) {
                return;
            }

            try {
                $request = request();
                $url = $request ? $request->fullUrl() : null;
                $message = (string) $e->getMessage();
                $fingerprint = hash('sha256', get_class($e).'|'.$message.'|'.$e->getFile().'|'.$e->getLine().'|'.$url);
                $requestId = (string) Str::uuid();

                SystemLog::create([
                    'channel' => 'server',
                    'level' => 'error',
                    'source' => 'laravel',
                    'message' => mb_substr($message, 0, 4000),
                    'exception_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'url' => $url,
                    'method' => $request ? $request->method() : null,
                    'user_id' => auth()->id(),
                    'ip_address' => $request ? $request->ip() : null,
                    'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 4000) : null,
                    'request_id' => $requestId,
                    'fingerprint' => $fingerprint,
                    'context' => LogSanitizer::sanitize([
                        'route' => $request ? optional($request->route())->getName() : null,
                        'input' => $request ? $request->all() : [],
                        'trace' => mb_substr($e->getTraceAsString(), 0, 12000),
                    ]),
                ]);
            } catch (\Throwable $ignored) {
                // fail-safe: never break exception reporting flow
            }
        });
    })->create();
