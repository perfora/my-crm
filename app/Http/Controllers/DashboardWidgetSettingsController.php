<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardWidgetSettingsController extends Controller
{
    public function index()
    {
        return view('dashboard-settings');
    }

    public function update(Request $request): RedirectResponse
    {
        $widgets = $request->input('widgets', []);
        $order = json_decode($request->input('order', '[]'), true);

        $settings = [];
        foreach (['ozet_kartlar', 'yillik_karsilastirma', 'bekleyen_isler', 'bu_ay_kazanilan', 'yuksek_oncelikli', 'yaklasan_ziyaretler'] as $key) {
            $settings[$key] = isset($widgets[$key]);
        }

        if (!empty($order)) {
            $settings['order'] = $order;
        }

        file_put_contents(storage_path('app/widget-settings.json'), json_encode($settings, JSON_PRETTY_PRINT));

        return redirect('/dashboard/widget-settings')->with('success', 'Widget ayarları kaydedildi!');
    }
}
