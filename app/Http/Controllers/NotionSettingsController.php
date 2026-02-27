<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class NotionSettingsController extends Controller
{
    public function index()
    {
        $settings = DB::table('notion_settings')->pluck('value', 'key')->toArray();

        return view('notion-settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|max:100',
            'value' => 'nullable|string',
        ]);

        $allowedKeys = [
            'api_token',
            'tum_isler_db_id',
            'musteriler_db_id',
            'kisiler_db_id',
            'ziyaretler_db_id',
            'markalar_db_id',
            'urunler_db_id',
            'fiyat_teklifleri_db_id',
        ];

        $key = $validated['key'];
        $value = $validated['value'] ?? '';
        if (!in_array($key, $allowedKeys, true)) {
            return redirect('/notion-settings')->with('error', 'Geçersiz ayar anahtarı.');
        }

        DB::table('notion_settings')
            ->where('key', $key)
            ->update(['value' => $value, 'updated_at' => now()]);

        if ($key === 'api_token') {
            try {
                $envFile = base_path('.env');
                if (is_file($envFile) && is_readable($envFile) && is_writable($envFile)) {
                    $envContent = file_get_contents($envFile);
                    if ($envContent !== false) {
                        if (str_contains($envContent, 'NOTION_API_TOKEN=')) {
                            $envContent = preg_replace('/NOTION_API_TOKEN=.*/', "NOTION_API_TOKEN={$value}", $envContent);
                        } else {
                            $envContent .= "\nNOTION_API_TOKEN={$value}\n";
                        }
                        file_put_contents($envFile, $envContent);
                    }
                }
            } catch (Exception $e) {
                // DB kaydı güncellenmiş olarak kalsın; .env yazımı başarısızsa UI'da sadece uyarı ver.
                return redirect('/notion-settings')->with('error', '.env güncellenemedi: ' . $e->getMessage());
            }
        }

        return redirect('/notion-settings')->with('success', 'Ayar kaydedildi!');
    }

    public function sync(Request $request): RedirectResponse
    {
        $type = $request->input('type');
        $settings = DB::table('notion_settings')->pluck('value', 'key')->toArray();
        $databaseId = $settings["{$type}_db_id"] ?? null;

        if (!$databaseId) {
            return redirect('/notion-settings')->with('error', 'Database ID bulunamadı!');
        }

        try {
            Artisan::call('notion:sync', [
                'database_id' => $databaseId,
                '--type' => $type,
            ]);

            $output = Artisan::output();
            return redirect('/notion-settings')->with('success', "✅ Sync tamamlandı!\n\n{$output}");
        } catch (Exception $e) {
            return redirect('/notion-settings')->with('error', '❌ Hata: ' . $e->getMessage());
        }
    }

    public function push(Request $request): RedirectResponse
    {
        $type = $request->input('type');
        $settings = DB::table('notion_settings')->pluck('value', 'key')->toArray();
        $databaseId = $settings["{$type}_db_id"] ?? null;

        if (!$databaseId) {
            return redirect('/notion-settings')->with('error', 'Database ID bulunamadı!');
        }

        try {
            Artisan::call('notion:push', [
                'database_id' => $databaseId,
                '--type' => $type,
            ]);

            $output = Artisan::output();
            return redirect('/notion-settings')->with('success', "✅ Push tamamlandı!\n\n{$output}");
        } catch (Exception $e) {
            return redirect('/notion-settings')->with('error', '❌ Hata: ' . $e->getMessage());
        }
    }
}
