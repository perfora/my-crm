<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChangeJournal;
use App\Models\SavedFilter;
use App\Models\SystemLog;
use App\Support\LogSanitizer;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TrackingController extends Controller
{
    public function savedFiltersIndex(Request $request)
    {
        $page = $request->input('page', 'tum-isler');
        return SavedFilter::where('page', $page)->get();
    }

    public function savedFiltersStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'page' => 'required|string',
            'filter_data' => 'required|array',
        ]);

        $filter = SavedFilter::create($validated);
        return response()->json($filter);
    }

    public function savedFiltersDestroy(Request $request, string $name): JsonResponse
    {
        $page = $request->input('page', 'tum-isler');
        SavedFilter::where('page', $page)->where('name', $name)->delete();

        return response()->json(['success' => true]);
    }

    public function clientErrors(Request $request): JsonResponse
    {
        $data = $request->validate([
            'level' => 'nullable|string|max:32',
            'source' => 'nullable|string|max:128',
            'message' => 'required|string|max:4000',
            'file' => 'nullable|string|max:1000',
            'line' => 'nullable|integer',
            'col' => 'nullable|integer',
            'stack' => 'nullable|string|max:12000',
            'url' => 'nullable|string|max:2000',
            'user_agent' => 'nullable|string|max:4000',
        ]);

        $fingerprint = hash(
            'sha256',
            ($data['message'] ?? '') . '|' . ($data['file'] ?? '') . '|' . ($data['line'] ?? '') . '|' . ($data['url'] ?? '')
        );

        SystemLog::create([
            'channel' => 'client',
            'level' => $data['level'] ?? 'error',
            'source' => $data['source'] ?? 'js',
            'message' => $data['message'],
            'file' => $data['file'] ?? null,
            'line' => $data['line'] ?? null,
            'url' => $data['url'] ?? $request->headers->get('referer'),
            'method' => 'CLIENT',
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $data['user_agent'] ?? $request->userAgent(),
            'request_id' => (string) Str::uuid(),
            'fingerprint' => $fingerprint,
            'context' => LogSanitizer::sanitize([
                'col' => $data['col'] ?? null,
                'stack' => $data['stack'] ?? null,
            ]),
        ]);

        return response()->json(['ok' => true]);
    }

    public static function clientErrorsMiddleware(): array
    {
        return [VerifyCsrfToken::class];
    }

    public function changeJournalsIndex(Request $request): JsonResponse
    {
        $query = ChangeJournal::query()->latest('id');
        if ($request->filled('task_key')) {
            $query->where('task_key', $request->string('task_key'));
        }

        return response()->json($query->limit(100)->get());
    }

    public function changeJournalsStore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'task_key' => 'nullable|string|max:128',
            'attempt_no' => 'nullable|integer|min:1',
            'actor' => 'required|string|max:64',
            'status' => 'required|in:pending,success,fail',
            'summary' => 'required|string|max:4000',
            'commit_hash' => 'nullable|string|max:64',
            'meta' => 'nullable|array',
        ]);

        $journal = ChangeJournal::create([
            'task_key' => $data['task_key'] ?? null,
            'attempt_no' => $data['attempt_no'] ?? 1,
            'actor' => $data['actor'],
            'status' => $data['status'],
            'summary' => $data['summary'],
            'commit_hash' => $data['commit_hash'] ?? null,
            'user_id' => auth()->id(),
            'meta' => LogSanitizer::sanitize($data['meta'] ?? []),
        ]);

        return response()->json($journal);
    }
}
