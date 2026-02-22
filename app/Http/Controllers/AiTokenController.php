<?php

namespace App\Http\Controllers;

use App\Models\AiApiToken;
use App\Models\AiAuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AiTokenController extends Controller
{
    public function index(): View
    {
        $tokens = AiApiToken::with('user')->orderByDesc('id')->get();
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        $logs = AiAuditLog::with('user', 'token')->orderByDesc('id')->limit(100)->get();

        return view('sistem-ai-api.index', compact('tokens', 'users', 'logs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'scopes' => 'required|array|min:1',
            'scopes.*' => 'in:crm.read',
            'expires_days' => 'required|integer|min:1|max:3650',
        ]);

        $rawToken = 'crm_ai_'.Str::random(48);

        $token = AiApiToken::create([
            'name' => $validated['name'],
            'user_id' => $validated['user_id'] ?? null,
            'token_hash' => hash('sha256', $rawToken),
            'scopes' => array_values(array_unique($validated['scopes'])),
            'is_active' => true,
            'expires_at' => now()->addDays((int) $validated['expires_days']),
        ]);

        return redirect()
            ->route('system.ai-api.index')
            ->with('success', 'AI token oluşturuldu.')
            ->with('generated_token', $rawToken)
            ->with('generated_token_id', $token->id);
    }

    public function toggle(int $id): RedirectResponse
    {
        $token = AiApiToken::findOrFail($id);
        $token->update([
            'is_active' => !$token->is_active,
        ]);

        return redirect()
            ->route('system.ai-api.index')
            ->with('success', $token->is_active ? 'Token aktif edildi.' : 'Token pasif edildi.');
    }
}

