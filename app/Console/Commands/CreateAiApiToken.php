<?php

namespace App\Console\Commands;

use App\Models\AiApiToken;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateAiApiToken extends Command
{
    protected $signature = 'ai:token-create
                            {name : Token adı}
                            {--user_id= : Bağlı kullanıcı ID}
                            {--scopes=crm.read : Virgülle ayrılmış scope listesi}
                            {--expires_days=30 : Kaç gün geçerli olacağı}';

    protected $description = 'AI API erişimi için yeni token üretir.';

    public function handle(): int
    {
        $userId = $this->option('user_id');
        $user = null;

        if ($userId !== null && $userId !== '') {
            $user = User::find($userId);
            if (!$user) {
                $this->error('Kullanıcı bulunamadı: '.$userId);
                return self::FAILURE;
            }
        }

        $tokenPlain = 'crm_ai_'.Str::random(48);
        $scopes = collect(explode(',', (string) $this->option('scopes')))
            ->map(fn ($s) => trim($s))
            ->filter()
            ->values()
            ->all();

        if (empty($scopes)) {
            $scopes = ['crm.read'];
        }

        $expiresDays = max((int) $this->option('expires_days'), 1);

        $token = AiApiToken::create([
            'user_id' => $user?->id,
            'name' => (string) $this->argument('name'),
            'token_hash' => hash('sha256', $tokenPlain),
            'scopes' => $scopes,
            'is_active' => true,
            'expires_at' => now()->addDays($expiresDays),
        ]);

        $this->info('Token oluşturuldu.');
        $this->line('ID: '.$token->id);
        $this->line('Scopes: '.implode(', ', $scopes));
        $this->line('Expires At: '.$token->expires_at);
        $this->newLine();
        $this->warn('Raw token bir kez gösterilir. Güvenli saklayın:');
        $this->line($tokenPlain);

        return self::SUCCESS;
    }
}

