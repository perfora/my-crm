<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teklif {{ $teklif->teklif_no }} - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
        #emailPreview {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        #emailPreview table {
            border-collapse: collapse;
            width: 100%;
        }
        #emailPreview th, #emailPreview td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        #emailPreview th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="no-print">
        @include('layouts.nav')
    </div>

    <div class="container mx-auto px-4 py-6 max-w-5xl">
        <!-- Toolbar -->
        <div class="flex justify-between items-center mb-6 no-print">
            <div>
                <a href="/fiyat-teklifleri" class="text-blue-600 hover:underline">&larr; Tekliflere Dön</a>
                <h1 class="text-2xl font-bold text-gray-800 mt-2">{{ $teklif->teklif_no }}</h1>
            </div>
            <div class="flex gap-2">
                <button onclick="window.print()" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg">
                    🖨️ Yazdır
                </button>
                <button onclick="copyEmailHTML()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    📧 Email HTML Kopyala
                </button>
                <button onclick="openOutlook()" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">
                    📨 Outlook'ta Aç
                </button>
            </div>
        </div>

        <!-- Email Preview -->
        <div id="emailPreview" class="bg-white rounded-lg shadow p-8">
            <!-- Header -->
            <div style="margin-bottom: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <h2 style="font-size: 24px; font-weight: bold; color: #1e40af; margin: 0;">FİYAT TEKLİFİ</h2>
                        <p style="margin: 5px 0; color: #666;">{{ $teklif->teklif_no }}</p>
                    </div>
                    @if($teklif->logo_path)
                    <div>
                        <img src="{{ $teklif->logo_path }}" alt="Logo" style="max-height: 80px;">
                    </div>
                    @endif
                </div>
            </div>

            <!-- Müşteri Bilgileri -->
            <div style="margin-bottom: 30px;">
                <table style="width: 100%; border: none;">
                    <tr style="border: none;">
                        <td style="border: none; vertical-align: top; width: 50%;">
                            <strong>Müşteri:</strong><br>
                            {{ $teklif->musteri->sirket }}<br>
                            @if($teklif->yetkili_adi)
                                <strong>Yetkili:</strong> {{ $teklif->yetkili_adi }}<br>
                            @endif
                            @if($teklif->yetkili_email)
                                <strong>E-mail:</strong> {{ $teklif->yetkili_email }}<br>
                            @endif
                        </td>
                        <td style="border: none; vertical-align: top; width: 50%; text-align: right;">
                            <strong>Tarih:</strong> {{ \Carbon\Carbon::parse($teklif->tarih)->format('d.m.Y') }}<br>
                            @if($teklif->gecerlilik_tarihi)
                                <strong>Geçerlilik:</strong> {{ \Carbon\Carbon::parse($teklif->gecerlilik_tarihi)->format('d.m.Y') }}<br>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Giriş Metni -->
            @if($teklif->giris_metni)
            <div style="margin-bottom: 30px;">
                <p style="margin: 0;">{{ $teklif->giris_metni }}</p>
            </div>
            @endif

            <!-- Ürün Tablosu -->
            <div style="margin-bottom: 30px;">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 40%;">Ürün Adı</th>
                            <th style="width: 10%; text-align: center;">Adet</th>
                            <th style="width: 15%; text-align: right;">Birim Fiyat</th>
                            <th style="width: 15%; text-align: right;">Toplam</th>
                            <th style="width: 10%; text-align: center;">Para Birimi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teklif->kalemler->sortBy('sira') as $index => $kalem)
                        <tr>
                            <td style="text-align: center;">{{ $index + 1 }}</td>
                            <td>{{ $kalem->urun_adi }}</td>
                            <td style="text-align: center;">{{ $kalem->adet }}</td>
                            <td style="text-align: right;">{{ number_format($kalem->satis_fiyat, 2) }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ number_format($kalem->satis_toplam, 2) }}</td>
                            <td style="text-align: center;">{{ $kalem->para_birimi }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-align: right; font-weight: bold; background-color: #f1f5f9;">TOPLAM TUTAR:</td>
                            <td style="text-align: right; font-weight: bold; font-size: 16px; background-color: #f1f5f9; color: #1e40af;">
                                {{ number_format($teklif->toplam_satis, 2) }}
                            </td>
                            <td style="text-align: center; background-color: #f1f5f9;">TL</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Ek Notlar -->
            @if($teklif->ek_notlar)
            <div style="margin-bottom: 30px;">
                <strong style="font-size: 14px; color: #1e40af;">Ek Notlar:</strong>
                <p style="margin: 10px 0; white-space: pre-wrap;">{{ $teklif->ek_notlar }}</p>
            </div>
            @endif

            <!-- Teklif Koşulları -->
            @if($teklif->teklif_kosullari)
            <div style="margin-bottom: 30px;">
                <strong style="font-size: 14px; color: #1e40af;">Teklif Koşulları:</strong>
                <p style="margin: 10px 0; white-space: pre-wrap;">{{ $teklif->teklif_kosullari }}</p>
            </div>
            @endif

            <!-- İmza -->
            <div style="margin-top: 50px;">
                @if($teklif->imza_path)
                <div style="text-align: right;">
                    <img src="{{ $teklif->imza_path }}" alt="İmza" style="max-height: 60px; margin-bottom: 10px;">
                </div>
                @endif
                <div style="text-align: right;">
                    <p style="margin: 5px 0;"><strong>Saygılarımızla,</strong></p>
                    <p style="margin: 5px 0; color: #666;">{{ config('app.name', 'Şirket Adı') }}</p>
                </div>
            </div>
        </div>

        <!-- Detaylı Bilgiler (Sadece CRM'de göster) -->
        <div class="bg-white rounded-lg shadow p-6 mt-6 no-print">
            <h3 class="text-lg font-semibold mb-4">Detaylı Bilgiler (Dahili)</h3>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-sm text-gray-600">Durum</p>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                        {{ $teklif->durum === 'Taslak' ? 'bg-gray-200 text-gray-800' : '' }}
                        {{ $teklif->durum === 'Gönderildi' ? 'bg-blue-200 text-blue-800' : '' }}
                        {{ $teklif->durum === 'Onaylandı' ? 'bg-green-200 text-green-800' : '' }}
                        {{ $teklif->durum === 'Reddedildi' ? 'bg-red-200 text-red-800' : '' }}">
                        {{ $teklif->durum }}
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Kar Oranı (Ortalama)</p>
                    <p class="font-semibold">{{ $teklif->kar_orani_varsayilan }}%</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Toplam Alış</p>
                    <p class="font-semibold">{{ number_format($teklif->toplam_alis, 2) }} TL</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Toplam Satış</p>
                    <p class="font-semibold text-green-600">{{ number_format($teklif->toplam_satis, 2) }} TL</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Toplam Kar</p>
                    <p class="font-semibold text-blue-600">{{ number_format($teklif->toplam_kar, 2) }} TL</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Kar Marjı</p>
                    <p class="font-semibold">{{ $teklif->toplam_alis > 0 ? number_format(($teklif->toplam_kar / $teklif->toplam_alis) * 100, 2) : 0 }}%</p>
                </div>
            </div>

            <div class="mt-6">
                <h4 class="font-semibold mb-2">Kalem Detayları</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ürün</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tedarikçi</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Alış</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Adet</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kar %</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Satış</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kar</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($teklif->kalemler->sortBy('sira') as $index => $kalem)
                            <tr>
                                <td class="px-3 py-2">{{ $index + 1 }}</td>
                                <td class="px-3 py-2">{{ $kalem->urun_adi }}</td>
                                <td class="px-3 py-2">{{ $kalem->tedarikci->sirket ?? '-' }}</td>
                                <td class="px-3 py-2">{{ number_format($kalem->alis_toplam, 2) }}</td>
                                <td class="px-3 py-2">{{ $kalem->adet }}</td>
                                <td class="px-3 py-2">{{ $kalem->kar_orani }}%</td>
                                <td class="px-3 py-2 font-semibold">{{ number_format($kalem->satis_toplam, 2) }}</td>
                                <td class="px-3 py-2 font-semibold text-blue-600">{{ number_format($kalem->satis_toplam - $kalem->alis_toplam, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyEmailHTML() {
            const emailContent = document.getElementById('emailPreview').innerHTML;
            
            // Tam HTML email şablonu
            const fullHTML = `<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
    </style>
</head>
<body>
    ${emailContent}
</body>
</html>`;

            navigator.clipboard.writeText(fullHTML).then(() => {
                alert('Email HTML kopyalandı! Outlook\'a yapıştırabilirsiniz.');
            }).catch(() => {
                // Fallback
                const textarea = document.createElement('textarea');
                textarea.value = fullHTML;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                alert('Email HTML kopyalandı! Outlook\'a yapıştırabilirsiniz.');
            });
        }

        function openOutlook() {
            // Önce HTML'i kopyala
            copyEmailHTML();
            
            // Sonra Outlook'u aç
            const subject = encodeURIComponent('Fiyat Teklifi - {{ $teklif->teklif_no }}');
            const to = '{{ $teklif->yetkili_email ?? "" }}';
            
            const mailtoLink = `mailto:${to}?subject=${subject}`;
            window.open(mailtoLink, '_blank');
            
            setTimeout(() => {
                alert('✅ Teklif HTML\' kopyalandı!\n\n📧 Outlook açıldı.\n\nYapmanız gerekenler:\n1. Outlook\'ta yeni mail oluştu\n2. İçerik alanına tıkla\n3. CTRL+V (veya CMD+V) ile yapıştır\n4. Gönder!');
            }, 1000);
        }
    </script>
</body>
</html>
