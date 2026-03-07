<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiService
{
    public function analyze(string $prompt): array
    {
        $apiKey = (string) config('services.openai.api_key');
        $model = (string) config('services.openai.model', 'gpt-4.1-mini');

        if ($apiKey === '') {
            throw new RuntimeException('OpenAI API anahtari tanimli degil.');
        }

        try {
            $response = Http::timeout(90)
                ->withToken($apiKey)
                ->acceptJson()
                ->post('https://api.openai.com/v1/responses', [
                    'model' => $model,
                    'input' => $prompt,
                ])
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            $message = $exception->response?->json('error.message')
                ?? $exception->getMessage();

            throw new RuntimeException('OpenAI istegi basarisiz: '.$message, previous: $exception);
        }

        $text = $response['output_text'] ?? null;
        if (!$text) {
            $text = collect($response['output'] ?? [])
                ->flatMap(fn (array $item) => $item['content'] ?? [])
                ->pluck('text')
                ->filter()
                ->implode("\n\n");
        }

        if (!$text) {
            throw new RuntimeException('OpenAI cevabindan metin cikmadi.');
        }

        return [
            'text' => $text,
            'meta' => [
                'model' => $response['model'] ?? $model,
                'response_id' => $response['id'] ?? null,
                'usage' => $response['usage'] ?? null,
                'created_at' => $response['created_at'] ?? null,
            ],
        ];
    }
}
