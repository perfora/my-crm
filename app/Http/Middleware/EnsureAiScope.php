<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAiScope
{
    public function handle(Request $request, Closure $next, string $scope): Response
    {
        $token = $request->attributes->get('ai_token');

        if (!$token || !$token->can($scope)) {
            return response()->json(['message' => 'Forbidden. Missing scope: '.$scope], 403);
        }

        return $next($request);
    }
}

