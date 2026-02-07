<?php

namespace App\Http\Controllers;

use App\Models\ChangeJournal;
use App\Models\FiyatTeklif;
use App\Models\Kisi;
use App\Models\Marka;
use App\Models\Musteri;
use App\Models\SystemLog;
use App\Models\TedarikiciFiyat;
use App\Models\TeklifKalem;
use App\Models\TeklifKosulu;
use App\Models\TumIsler;
use App\Models\Urun;
use App\Models\Ziyaret;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;
use ZipArchive;

class SystemExportController extends Controller
{
    public function index()
    {
        return view('sistem-export.index', [
            'datasets' => $this->datasetDefinitions(),
        ]);
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'datasets' => 'required|array|min:1',
            'datasets.*' => 'string',
        ]);

        $definitions = $this->datasetDefinitions();
        $selected = collect($validated['datasets'])
            ->filter(fn ($key) => isset($definitions[$key]))
            ->values()
            ->all();

        if (empty($selected)) {
            return back()->withErrors(['datasets' => 'Geçerli en az bir veri seti seçin.']);
        }

        if (count($selected) === 1) {
            $key = $selected[0];
            $csv = $this->buildCsv($definitions[$key]['headers'], ($definitions[$key]['rows'])());
            $filename = $definitions[$key]['filename'].'_'.now()->format('Ymd_His').'.csv';

            return Response::make($csv, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        }

        return $this->exportAsZip($selected, $definitions);
    }

    private function exportAsZip(array $selected, array $definitions)
    {
        if (!class_exists(ZipArchive::class)) {
            $merged = "\xEF\xBB\xBF";
            foreach ($selected as $key) {
                $def = $definitions[$key];
                $merged .= $def['label']."\n";
                $merged .= $this->buildCsv($def['headers'], ($def['rows'])(), false)."\n\n";
            }

            return Response::make($merged, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="crm_export_'.now()->format('Ymd_His').'.csv"',
            ]);
        }

        $zipPath = storage_path('app/tmp/crm_export_'.now()->format('Ymd_His').'_'.uniqid().'.zip');
        if (!is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0775, true);
        }

        $zip = new ZipArchive();
        $open = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($open !== true) {
            return back()->withErrors(['datasets' => 'ZIP dosyası oluşturulamadı.']);
        }

        foreach ($selected as $key) {
            $def = $definitions[$key];
            $csv = $this->buildCsv($def['headers'], ($def['rows'])());
            $zip->addFromString($def['filename'].'.csv', $csv);
        }

        $zip->close();

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    private function buildCsv(array $headers, array $rows, bool $addBom = true): string
    {
        $stream = fopen('php://temp', 'r+');
        if ($addBom) {
            fwrite($stream, "\xEF\xBB\xBF");
        }

        fputcsv($stream, $headers, ';');
        foreach ($rows as $row) {
            fputcsv($stream, $this->normalizeRow($row, $headers), ';');
        }

        rewind($stream);
        $content = stream_get_contents($stream) ?: '';
        fclose($stream);

        return $content;
    }

    private function normalizeRow(array $row, array $headers): array
    {
        $normalized = [];
        foreach ($headers as $key) {
            $value = $row[$key] ?? null;
            if ($value instanceof Carbon) {
                $value = $value->format('Y-m-d H:i:s');
            }
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
            $normalized[] = $value ?? '';
        }

        return $normalized;
    }

    private function datasetDefinitions(): array
    {
        return [
            'tum_isler' => [
                'label' => 'Tüm İşler',
                'filename' => 'tum_isler',
                'headers' => ['id', 'name', 'musteri', 'marka', 'tipi', 'turu', 'oncelik', 'register_durum', 'teklif_tutari', 'teklif_doviz', 'alis_tutari', 'alis_doviz', 'kapanis_tarihi', 'lisans_bitis', 'is_guncellenme_tarihi', 'notlar', 'created_at', 'updated_at'],
                'rows' => fn () => TumIsler::query()->with(['musteri:id,sirket', 'marka:id,name'])->get()->map(fn ($i) => [
                    'id' => $i->id,
                    'name' => $i->name,
                    'musteri' => $i->musteri?->sirket,
                    'marka' => $i->marka?->name,
                    'tipi' => $i->tipi,
                    'turu' => $i->turu,
                    'oncelik' => $i->oncelik,
                    'register_durum' => $i->register_durum,
                    'teklif_tutari' => $i->teklif_tutari,
                    'teklif_doviz' => $i->teklif_doviz,
                    'alis_tutari' => $i->alis_tutari,
                    'alis_doviz' => $i->alis_doviz,
                    'kapanis_tarihi' => $i->kapanis_tarihi,
                    'lisans_bitis' => $i->lisans_bitis,
                    'is_guncellenme_tarihi' => $i->is_guncellenme_tarihi,
                    'notlar' => $i->notlar,
                    'created_at' => $i->created_at,
                    'updated_at' => $i->updated_at,
                ])->all(),
            ],
            'musteriler' => [
                'label' => 'Firmalar',
                'filename' => 'musteriler',
                'headers' => ['id', 'sirket', 'sehir', 'telefon', 'derece', 'turu', 'adres', 'notlar', 'created_at', 'updated_at'],
                'rows' => fn () => Musteri::query()->get(['id', 'sirket', 'sehir', 'telefon', 'derece', 'turu', 'adres', 'notlar', 'created_at', 'updated_at'])->map->toArray()->all(),
            ],
            'markalar' => [
                'label' => 'Markalar',
                'filename' => 'markalar',
                'headers' => ['id', 'name', 'created_at', 'updated_at'],
                'rows' => fn () => Marka::query()->get(['id', 'name', 'created_at', 'updated_at'])->map->toArray()->all(),
            ],
            'kisiler' => [
                'label' => 'Kişiler',
                'filename' => 'kisiler',
                'headers' => ['id', 'ad_soyad', 'musteri', 'telefon_numarasi', 'email_adresi', 'bolum', 'gorev', 'created_at', 'updated_at'],
                'rows' => fn () => Kisi::query()->with('musteri:id,sirket')->get()->map(fn ($k) => [
                    'id' => $k->id,
                    'ad_soyad' => $k->ad_soyad,
                    'musteri' => $k->musteri?->sirket,
                    'telefon_numarasi' => $k->telefon_numarasi,
                    'email_adresi' => $k->email_adresi,
                    'bolum' => $k->bolum,
                    'gorev' => $k->gorev,
                    'created_at' => $k->created_at,
                    'updated_at' => $k->updated_at,
                ])->all(),
            ],
            'ziyaretler' => [
                'label' => 'Ziyaretler',
                'filename' => 'ziyaretler',
                'headers' => ['id', 'ziyaret_ismi', 'musteri', 'ziyaret_tarihi', 'arama_tarihi', 'tur', 'durumu', 'ziyaret_notlari', 'created_at', 'updated_at'],
                'rows' => fn () => Ziyaret::query()->with('musteri:id,sirket')->get()->map(fn ($z) => [
                    'id' => $z->id,
                    'ziyaret_ismi' => $z->ziyaret_ismi,
                    'musteri' => $z->musteri?->sirket,
                    'ziyaret_tarihi' => $z->ziyaret_tarihi,
                    'arama_tarihi' => $z->arama_tarihi,
                    'tur' => $z->tur,
                    'durumu' => $z->durumu,
                    'ziyaret_notlari' => $z->ziyaret_notlari,
                    'created_at' => $z->created_at,
                    'updated_at' => $z->updated_at,
                ])->all(),
            ],
            'fiyat_teklifleri' => [
                'label' => 'Fiyat Teklifleri',
                'filename' => 'fiyat_teklifleri',
                'headers' => ['id', 'teklif_no', 'musteri', 'yetkili_adi', 'yetkili_email', 'tarih', 'gecerlilik_tarihi', 'durum', 'toplam_alis', 'toplam_satis', 'toplam_kar', 'created_at', 'updated_at'],
                'rows' => fn () => FiyatTeklif::query()->with('musteri:id,sirket')->get()->map(fn ($t) => [
                    'id' => $t->id,
                    'teklif_no' => $t->teklif_no,
                    'musteri' => $t->musteri?->sirket,
                    'yetkili_adi' => $t->yetkili_adi,
                    'yetkili_email' => $t->yetkili_email,
                    'tarih' => $t->tarih,
                    'gecerlilik_tarihi' => $t->gecerlilik_tarihi,
                    'durum' => $t->durum,
                    'toplam_alis' => $t->toplam_alis,
                    'toplam_satis' => $t->toplam_satis,
                    'toplam_kar' => $t->toplam_kar,
                    'created_at' => $t->created_at,
                    'updated_at' => $t->updated_at,
                ])->all(),
            ],
            'teklif_kalemleri' => [
                'label' => 'Teklif Kalemleri',
                'filename' => 'teklif_kalemleri',
                'headers' => ['id', 'teklif_id', 'teklif_no', 'tedarikci', 'urun', 'urun_adi', 'adet', 'alis_fiyat', 'alis_toplam', 'satis_fiyat', 'satis_toplam', 'para_birimi', 'notlar', 'created_at', 'updated_at'],
                'rows' => fn () => TeklifKalem::query()->with(['teklif:id,teklif_no', 'tedarikci:id,sirket', 'urun:id,urun_adi'])->get()->map(fn ($k) => [
                    'id' => $k->id,
                    'teklif_id' => $k->teklif_id,
                    'teklif_no' => $k->teklif?->teklif_no,
                    'tedarikci' => $k->tedarikci?->sirket,
                    'urun' => $k->urun?->urun_adi,
                    'urun_adi' => $k->urun_adi,
                    'adet' => $k->adet,
                    'alis_fiyat' => $k->alis_fiyat,
                    'alis_toplam' => $k->alis_toplam,
                    'satis_fiyat' => $k->satis_fiyat,
                    'satis_toplam' => $k->satis_toplam,
                    'para_birimi' => $k->para_birimi,
                    'notlar' => $k->notlar,
                    'created_at' => $k->created_at,
                    'updated_at' => $k->updated_at,
                ])->all(),
            ],
            'teklif_kosullari' => [
                'label' => 'Teklif Koşulları',
                'filename' => 'teklif_kosullari',
                'headers' => ['id', 'baslik', 'icerik', 'varsayilan', 'sira', 'created_at', 'updated_at'],
                'rows' => fn () => TeklifKosulu::query()->get(['id', 'baslik', 'icerik', 'varsayilan', 'sira', 'created_at', 'updated_at'])->map->toArray()->all(),
            ],
            'urunler' => [
                'label' => 'Ürünler',
                'filename' => 'urunler',
                'headers' => ['id', 'urun_adi', 'marka', 'kategori', 'stok_kodu', 'son_alis_fiyat', 'ortalama_kar_orani', 'notlar', 'created_at', 'updated_at'],
                'rows' => fn () => Urun::query()->with('marka:id,name')->get()->map(fn ($u) => [
                    'id' => $u->id,
                    'urun_adi' => $u->urun_adi,
                    'marka' => $u->marka?->name,
                    'kategori' => $u->kategori,
                    'stok_kodu' => $u->stok_kodu,
                    'son_alis_fiyat' => $u->son_alis_fiyat,
                    'ortalama_kar_orani' => $u->ortalama_kar_orani,
                    'notlar' => $u->notlar,
                    'created_at' => $u->created_at,
                    'updated_at' => $u->updated_at,
                ])->all(),
            ],
            'tedarikci_fiyatlari' => [
                'label' => 'Tedarikçi Fiyatları',
                'filename' => 'tedarikci_fiyatlari',
                'headers' => ['id', 'tedarikci', 'urun', 'urun_adi', 'tarih', 'birim_fiyat', 'para_birimi', 'minimum_siparis', 'temin_suresi', 'aktif', 'notlar', 'created_at', 'updated_at'],
                'rows' => fn () => TedarikiciFiyat::query()->with(['tedarikci:id,sirket', 'urun:id,urun_adi'])->get()->map(fn ($f) => [
                    'id' => $f->id,
                    'tedarikci' => $f->tedarikci?->sirket,
                    'urun' => $f->urun?->urun_adi,
                    'urun_adi' => $f->urun_adi,
                    'tarih' => $f->tarih,
                    'birim_fiyat' => $f->birim_fiyat,
                    'para_birimi' => $f->para_birimi,
                    'minimum_siparis' => $f->minimum_siparis,
                    'temin_suresi' => $f->temin_suresi,
                    'aktif' => $f->aktif,
                    'notlar' => $f->notlar,
                    'created_at' => $f->created_at,
                    'updated_at' => $f->updated_at,
                ])->all(),
            ],
            'sistem_loglari' => [
                'label' => 'Sistem Logları',
                'filename' => 'sistem_loglari',
                'headers' => ['id', 'channel', 'level', 'source', 'message', 'url', 'method', 'user_id', 'ip_address', 'exception_class', 'file', 'line', 'created_at'],
                'rows' => fn () => SystemLog::query()->latest('id')->limit(5000)->get([
                    'id', 'channel', 'level', 'source', 'message', 'url', 'method', 'user_id', 'ip_address', 'exception_class', 'file', 'line', 'created_at',
                ])->map->toArray()->all(),
            ],
            'degisiklik_gecmisi' => [
                'label' => 'Değişiklik Geçmişi',
                'filename' => 'degisiklik_gecmisi',
                'headers' => ['id', 'task_key', 'attempt_no', 'actor', 'status', 'summary', 'commit_hash', 'user_id', 'created_at'],
                'rows' => fn () => ChangeJournal::query()->latest('id')->limit(5000)->get([
                    'id', 'task_key', 'attempt_no', 'actor', 'status', 'summary', 'commit_hash', 'user_id', 'created_at',
                ])->map->toArray()->all(),
            ],
        ];
    }
}
