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
            <div style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #e5e7eb;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="font-size: 24px; font-weight: bold; color: #1e40af; margin: 0;">FİYAT TEKLİFİ</h2>
                    </div>
                    <div>
                        <img src="/Netcom_logo.png" alt="Logo" style="max-height: 80px;">
                    </div>
                </div>
            </div>

            <!-- Müşteri Bilgileri -->
            <div style="margin-bottom: 30px;">
                <table style="width: 100%; border: none;">
                    <tr style="border: none;">
                        <td style="border: none; vertical-align: top; width: 50%;">
                            <strong>Müşteri:</strong> {{ $teklif->musteri->sirket }}
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
            <div style="margin-bottom: 30px;">
                <p style="margin: 0; line-height: 1.8;">
                    <strong>Sayın @php
                        $isim = $teklif->yetkili_adi;
                        if ($isim) {
                            $parcalar = explode(' ', trim($isim));
                            echo $parcalar[0];
                        } else {
                            echo 'Yetkili';
                        }
                    @endphp Bey,</strong><br><br>
                    Yapılacak olan alımınızla ilgili hazırlamış olduğumuz fiyat teklifimizi aşağıda bulabilirsiniz. Teklifimizi uygun bulacağınızı ümit eder, teklif ile ilgili her türlü tamamlayıcı bilgi ve görüş için bizi arayabileceğinizi belirtmek isteriz.<br><br>
                    Saygılarımızla.
                </p>
                
                <!-- İmza -->
                <div style="text-align: right; margin-top: 30px; margin-bottom: 20px;">
                    <p style="margin: 5px 0; font-weight: bold; font-size: 14px;">MURAT PEKTAŞ</p>
                    <p style="margin: 5px 0; color: #666; font-size: 12px;">Proje Yöneticisi</p>
                    <p style="margin: 5px 0; color: #666; font-size: 12px;">0549 476 38 00</p>
                </div>
            </div>

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
                            <td style="text-align: center; background-color: #f1f5f9;">
                                @php
                                    $paraBirimleri = $teklif->kalemler->pluck('para_birimi')->unique();
                                    echo $paraBirimleri->count() === 1 ? $paraBirimleri->first() : 'Karışık';
                                @endphp
                            </td>
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
                <p style="margin: 0; white-space: pre-wrap; line-height: 1.8;">{{ $teklif->teklif_kosullari }}</p>
            </div>
            @endif
        </div> <!-- emailPreview kapandı -->
    </div> <!-- container kapandı -->

    <script>
        function copyEmailHTML() {
            const emailContent = document.getElementById('emailPreview').innerHTML;
            
            const fullHTML = '<!DOCTYPE html>\n<html lang="tr">\n<head>\n    <meta charset="UTF-8">\n    <meta name="viewport" content="width=device-width, initial-scale=1.0">\n    <style>\n        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }\n        table { border-collapse: collapse; width: 100%; }\n        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\n        th { background-color: #f8f9fa; font-weight: bold; }\n    </style>\n</head>\n<body>\n' + emailContent + '\n</body>\n</html>';

            navigator.clipboard.writeText(fullHTML).then(function() {
                alert('Email HTML kopyalandı! Outlook\'a yapıştırabilirsiniz.');
            }).catch(function() {
                const textarea = document.createElement('textarea');
                textarea.value = fullHTML;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                alert('Email HTML kopyalandı!');
            });
        }

        function openOutlook() {
            copyEmailHTML();
            const subject = encodeURIComponent('Fiyat Teklifi - {{ $teklif->teklif_no }}');
            const to = '{{ $teklif->yetkili_email ?? "" }}';
            const mailtoLink = 'mailto:' + to + '?subject=' + subject;
            window.open(mailtoLink, '_blank');
            
            setTimeout(function() {
                alert('Teklif HTML kopyalandı! Outlook açıldı. İçeriği yapıştırıp gönderebilirsiniz.');
            }, 1000);
        }
    </script>
</body>
</html>