<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Teklif - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        .select2-container--default .select2-selection--single {
            height: 42px;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 26px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }
    </style>
</head>
<body class="bg-gray-50">
    @include('layouts.nav')

    <div class="container mx-auto px-4 py-6 max-w-6xl">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Yeni Fiyat Teklifi</h1>
        </div>

        <form id="teklifForm" onsubmit="handleSubmit(event)">
            <!-- Temel Bilgiler -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">Temel Bilgiler</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Teklif No *</label>
                        <input type="text" name="teklif_no" value="{{ $teklifNo }}" readonly
                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tarih *</label>
                        <input type="date" name="tarih" value="{{ date('Y-m-d') }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Müşteri *</label>
                        <select id="musteriSelect" name="musteri_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Müşteri seçin...</option>
                            @foreach($musteriler as $musteri)
                                <option value="{{ $musteri->id }}">{{ $musteri->sirket }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Yetkili</label>
                        <select id="yetkiliSelect" name="yetkili_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Yetkili seçin...</option>
                        </select>
                        <input type="hidden" name="yetkili_adi" id="yetkiliAdi">
                        <input type="hidden" name="yetkili_email" id="yetkiliEmail">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Geçerlilik Tarihi</label>
                        <input type="date" name="gecerlilik_tarihi"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Varsayılan Kar Oranı (%)</label>
                        <input type="number" name="kar_orani_varsayilan" value="25" min="0" max="1000"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Giriş Metni</label>
                    <textarea name="giris_metni" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Sayın yetkili, talebiniz doğrultusunda hazırladığımız fiyat teklifimiz aşağıdaki gibidir..."></textarea>
                </div>
            </div>

            <!-- Kalemler -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">Teklif Kalemleri</h2>
                    <button type="button" onclick="addKalem()" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">
                        + Kalem Ekle
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border" id="kalemlerTable">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 uppercase" style="width: 3%;">#</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 uppercase" style="width: 25%;">Ürün *</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 uppercase" style="width: 15%;">Tedarikçi</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 uppercase" style="width: 10%;">Alış Fiyat *</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 uppercase" style="width: 7%;">Adet *</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 uppercase" style="width: 10%;">Alış Toplam</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 uppercase" style="width: 7%;">Kar % *</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 uppercase" style="width: 10%;">Satış Fiyat</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 uppercase" style="width: 10%;">Satış Toplam</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 uppercase" style="width: 8%;">Para Birimi</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-700 uppercase" style="width: 5%;">İşlem</th>
                            </tr>
                        </thead>
                        <tbody id="kalemlerBody" class="bg-white divide-y divide-gray-200">
                            <!-- Kalemler buraya eklenecek -->
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-right font-semibold">TOPLAM:</td>
                                <td class="px-4 py-3 font-semibold" id="toplamAlis">0.00 TL</td>
                                <td class="px-4 py-3"></td>
                                <td class="px-4 py-3"></td>
                                <td class="px-4 py-3 font-semibold text-green-600" id="toplamSatis">0.00 TL</td>
                                <td colspan="2" class="px-4 py-3"></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-right font-semibold">KAR:</td>
                                <td colspan="4" class="px-4 py-3 font-semibold text-blue-600" id="toplamKar">0.00 TL</td>
                                <td colspan="2" class="px-4 py-3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Ek Bilgiler -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">Ek Bilgiler</h2>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ek Notlar</label>
                    <textarea name="ek_notlar" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Teklif Koşulları</label>
                    <div class="flex gap-2 mb-2">
                        <select id="kosulSablonSelect" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Hazır şablon seç...</option>
                        </select>
                        <a href="/teklif-kosullari" target="_blank" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 whitespace-nowrap">
                            ⚙️ Yönet
                        </a>
                    </div>
                    <textarea id="kosulTextarea" name="teklif_kosullari" rows="8"></textarea>
                </div>
            </div>

            <!-- Kaydet -->
            <div class="flex justify-end gap-3">
                <a href="/fiyat-teklifleri" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                    İptal
                </a>
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    💾 Teklifi Kaydet
                </button>
            </div>
        </form>
    </div>

    <script>
        const urunler = @json($urunler);
        const tedarikciler = @json($tedarikciler);
        let kalemSayisi = 0;

        $(document).ready(function() {
            $('#musteriSelect').select2({
                placeholder: 'Müşteri seçin...',
                language: { noResults: () => "Sonuç bulunamadı", searching: () => "Aranıyor..." }
            }).on('change', function() {
                loadYetkililer($(this).val());
            });

            // İlk kalem ekle
            addKalem();
        });

        function loadYetkililer(musteriId) {
            if (!musteriId) {
                $('#yetkiliSelect').html('<option value="">Yetkili seçin...</option>');
                return;
            }

            $.get(`/api/musteriler/${musteriId}/yetkililer`, function(yetkililer) {
                let options = '<option value="">Yetkili seçin...</option>';
                yetkililer.forEach(y => {
                    options += `<option value="${y.id}" data-email="${y.email_adresi || ''}">${y.ad_soyad}</option>`;
                });
                $('#yetkiliSelect').html(options);
            });
        }

        $('#yetkiliSelect').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            $('#yetkiliAdi').val(selectedOption.text());
            $('#yetkiliEmail').val(selectedOption.data('email') || '');
        });

        function addKalem() {
            kalemSayisi++;
            const karOrani = $('[name="kar_orani_varsayilan"]').val() || 25;

            const row = `
                <tr data-kalem="${kalemSayisi}">
                    <td class="px-2 py-2 text-center">${kalemSayisi}</td>
                    <td class="px-2 py-2">
                        <select class="urun-select border border-gray-300 rounded px-2 py-1 w-full" style="min-width: 200px;" data-kalem="${kalemSayisi}" required>
                            <option value="">Ürün seçin veya yazın...</option>
                            ${urunler.map(u => {
                                let displayText = u.urun_adi;
                                if (u.marka) displayText += ' - ' + u.marka.marka_adi;
                                if (u.kategori) displayText += ' [' + u.kategori + ']';
                                if (u.stok_kodu) displayText += ' (' + u.stok_kodu + ')';
                                return `<option value="${u.id}" data-fiyat="${u.son_alis_fiyat || 0}" data-kar="${u.ortalama_kar_orani || 25}">${displayText}</option>`;
                            }).join('')}
                        </select>
                        <input type="hidden" name="kalemler[${kalemSayisi}][urun_id]" class="urun-id">
                        <input type="hidden" name="kalemler[${kalemSayisi}][urun_adi]" class="urun-adi" required>
                    </td>
                    <td class="px-2 py-2">
                        <select class="tedarikci-select border border-gray-300 rounded px-2 py-1 w-full text-sm" name="kalemler[${kalemSayisi}][musteri_id]">
                            <option value="">Seç...</option>
                            ${tedarikciler.map(t => `<option value="${t.id}">${t.sirket}</option>`).join('')}
                        </select>
                    </td>
                    <td class="px-2 py-2">
                        <input type="number" step="0.01" min="0" name="kalemler[${kalemSayisi}][alis_fiyat]" 
                            class="alis-fiyat border border-gray-300 rounded px-2 py-1 w-full" required onchange="hesaplaKalem(${kalemSayisi})">
                    </td>
                    <td class="px-2 py-2">
                        <input type="number" min="1" name="kalemler[${kalemSayisi}][adet]" value="1"
                            class="adet border border-gray-300 rounded px-2 py-1 w-full" required onchange="hesaplaKalem(${kalemSayisi})">
                    </td>
                    <td class="px-2 py-2 alis-toplam font-semibold text-sm">0.00</td>
                    <td class="px-2 py-2">
                        <input type="number" min="0" name="kalemler[${kalemSayisi}][kar_orani]" value="${karOrani}"
                            class="kar-orani border border-gray-300 rounded px-2 py-1 w-full" required onchange="hesaplaKalem(${kalemSayisi})">
                    </td>
                    <td class="px-2 py-2 satis-fiyat font-semibold text-sm">0.00</td>
                    <td class="px-2 py-2 satis-toplam font-semibold text-green-600 text-sm">0.00</td>
                    <td class="px-2 py-2">
                        <select name="kalemler[${kalemSayisi}][para_birimi]" class="border border-gray-300 rounded px-2 py-1 w-full text-sm" required onchange="hesaplaToplamlar()">
                            <option value="TL">TL</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                        </select>
                    </td>
                    <td class="px-4 py-2">
                        <button type="button" onclick="removeKalem(${kalemSayisi})" class="text-red-600 hover:text-red-800">Sil</button>
                    </td>
                </tr>
            `;

            $('#kalemlerBody').append(row);

            // Ürün select'i initialize et
            $(`.urun-select[data-kalem="${kalemSayisi}"]`).select2({
                placeholder: 'Ürün seçin veya yazın...',
                tags: true,
                language: { noResults: () => "Yeni ürün eklemek için yazın", searching: () => "Aranıyor..." }
            }).on('change', function() {
                const kalemNo = $(this).data('kalem');
                const row = $(`tr[data-kalem="${kalemNo}"]`);
                const selectedOption = $(this).find('option:selected');

                if ($(this).val() && !selectedOption.data('fiyat')) {
                    // Yeni ürün
                    row.find('.urun-id').val('');
                    row.find('.urun-adi').val($(this).val());
                } else {
                    // Mevcut ürün
                    row.find('.urun-id').val($(this).val());
                    row.find('.urun-adi').val(selectedOption.text());
                    row.find('.alis-fiyat').val(selectedOption.data('fiyat') || 0);
                    // Kar oranını da güncelle
                    const karOrani = selectedOption.data('kar') || 25;
                    row.find('.kar-orani').val(karOrani);
                }
                hesaplaKalem(kalemNo);
            });
        }

        function removeKalem(kalemNo) {
            $(`tr[data-kalem="${kalemNo}"]`).remove();
            hesaplaToplamlar();
        }

        function hesaplaKalem(kalemNo) {
            const row = $(`tr[data-kalem="${kalemNo}"]`);
            const alisFiyat = parseFloat(row.find('.alis-fiyat').val()) || 0;
            const adet = parseInt(row.find('.adet').val()) || 1;
            const karOrani = parseFloat(row.find('.kar-orani').val()) || 0;

            const alisToplam = alisFiyat * adet;
            const satisFiyat = alisFiyat * (1 + karOrani / 100);
            const satisToplam = satisFiyat * adet;

            row.find('.alis-toplam').text(alisToplam.toFixed(2));
            row.find('.satis-fiyat').text(satisFiyat.toFixed(2));
            row.find('.satis-toplam').text(satisToplam.toFixed(2));

            hesaplaToplamlar();
        }

        function hesaplaToplamlar() {
            let toplamAlis = 0;
            let toplamSatis = 0;
            let paraBirimleri = new Set();

            $('#kalemlerBody tr').each(function() {
                const row = $(this);
                const alisToplam = parseFloat(row.find('.alis-toplam').text()) || 0;
                const satisToplam = parseFloat(row.find('.satis-toplam').text()) || 0;
                const paraBirimi = row.find('select[name*="para_birimi"]').val();
                
                toplamAlis += alisToplam;
                toplamSatis += satisToplam;
                if (paraBirimi) paraBirimleri.add(paraBirimi);
            });

            const toplamKar = toplamSatis - toplamAlis;
            
            // Para birimi belirle
            let birimText = 'TL';
            if (paraBirimleri.size === 1) {
                birimText = Array.from(paraBirimleri)[0];
            } else if (paraBirimleri.size > 1) {
                birimText = 'Karışık';
            }

            $('#toplamAlis').text(toplamAlis.toFixed(2) + ' ' + birimText);
            $('#toplamSatis').text(toplamSatis.toFixed(2) + ' ' + birimText);
            $('#toplamKar').text(toplamKar.toFixed(2) + ' ' + birimText);
        }

        function handleSubmit(e) {
            e.preventDefault();

            if ($('#kalemlerBody tr').length === 0) {
                alert('En az bir kalem eklemelisiniz!');
                return;
            }

            const formData = new FormData(e.target);
            const data = {};

            formData.forEach((value, key) => {
                if (key.includes('[')) {
                    const match = key.match(/kalemler\[(\d+)\]\[(.+)\]/);
                    if (match) {
                        if (!data.kalemler) data.kalemler = [];
                        const index = parseInt(match[1]) - 1;
                        if (!data.kalemler[index]) data.kalemler[index] = {};
                        data.kalemler[index][match[2]] = value;
                    }
                } else {
                    data[key] = value;
                }
            });

            // Boş kalemleri temizle
            data.kalemler = data.kalemler.filter(k => k && k.urun_adi);

            fetch('/fiyat-teklifleri', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert(result.message);
                    window.location.href = '/fiyat-teklifleri';
                } else {
                    alert('Hata: ' + (result.message || 'Kayıt yapılamadı'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Bir hata oluştu!');
            });
        }

        // TinyMCE başlat
        let kosulEditor;
        tinymce.init({
            selector: '#kosulTextarea',
            height: 300,
            menubar: false,
            plugins: 'lists link paste',
            toolbar: 'undo redo | bold italic underline | bullist numlist | link | removeformat',
            paste_as_text: false,
            paste_word_valid_elements: 'b,strong,i,em,u,p,br,ul,ol,li',
            setup: function(ed) {
                kosulEditor = ed;
            },
            content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; }'
        });

        // API'den teklif koşullarını yükle
        $.get('/api/teklif-kosullari', function(data) {
            const select = $('#kosulSablonSelect');
            select.empty();
            select.append('<option value="">Hazır şablon seç...</option>');
            
            data.forEach(function(kosul) {
                select.append(`<option value="${kosul.id}">${kosul.baslik}</option>`);
                
                // Varsayılan olanı seç ve yükle
                if (kosul.varsayilan && kosulEditor) {
                    setTimeout(function() {
                        kosulEditor.setContent(kosul.icerik);
                    }, 500);
                }
            });
        });

        // Teklif koşulları şablon seçimi
        $('#kosulSablonSelect').on('change', function() {
            const kosulId = $(this).val();
            if (!kosulId) return;
            
            $.get('/api/teklif-kosullari', function(data) {
                const secili = data.find(k => k.id == kosulId);
                if (secili && kosulEditor) {
                    kosulEditor.setContent(secili.icerik);
                }
            });
        });
    </script>
</body>
</html>
