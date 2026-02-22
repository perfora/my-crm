<?php

namespace App\Http\Middleware;

use App\Models\AiApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = (string) $request->header('Authorization', '');

        if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return response()->json(['message' => 'Unauthorized. Missing Bearer token.'], 401);
        }

        $rawToken = trim($matches[1]);
        if ($rawToken === '') {
            return response()->json(['message' => 'Unauthorized. Empty token.'], 401);
        }

        $tokenHash = hash('sha256', $rawToken);
        $token = AiApiToken::with('user')
            ->where('token_hash', $tokenHash)
            ->first();

        if (!$token || !$token->is_active || $token->isExpired()) {
            return response()->json(['message' => 'Unauthorized. Invalid or expired token.'], 401);
        }

        $token->forceFill(['last_used_at' => now()])->save();

        if ($token->user_id) {
            auth()->setUser($token->user);
        }

        $request->attributes->set('ai_token', $token);

        return $next($request);
    }
}

