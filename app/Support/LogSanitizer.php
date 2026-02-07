<?php

namespace App\Support;

class LogSanitizer
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'token',
        'access_token',
        'refresh_token',
        'authorization',
        'cookie',
        'secret',
        'api_key',
        'key',
    ];

    public static function sanitize(mixed $value): mixed
    {
        if (is_array($value)) {
            $sanitized = [];
            foreach ($value as $k => $v) {
                $key = is_string($k) ? strtolower($k) : (string) $k;
                if (self::isSensitiveKey($key)) {
                    $sanitized[$k] = '***';
                    continue;
                }
                $sanitized[$k] = self::sanitize($v);
            }
            return $sanitized;
        }

        if (is_object($value)) {
            return self::sanitize((array) $value);
        }

        if (is_string($value) && strlen($value) > 4000) {
            return substr($value, 0, 4000).'... [truncated]';
        }

        return $value;
    }

    private static function isSensitiveKey(string $key): bool
    {
        foreach (self::SENSITIVE_KEYS as $sensitive) {
            if (str_contains($key, $sensitive)) {
                return true;
            }
        }
        return false;
    }
}

