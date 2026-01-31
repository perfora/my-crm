<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raporlar - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .sortable {
            cursor: pointer;
            user-select: none;
            transition: background-color 0.2s;
        }
        .sortable:hover {
            background-color: #e5e7eb;
        }
        .sort-icon {
            font-size: 0.75rem;
            margin-left: 4px;
        }
    </style>
</head>
<body class="bg-gray-100">
    @include('layouts.nav')
    
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">📊 Raporlar</h1>

        <!-- Yıl Seçimi -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Yıl Seçin</label>
                    <select id="yil-secim" class="w-full border rounded px-3 py-2">
                        @php
                            $currentYear = date('Y');
                            for($y = $currentYear; $y >= 2020; $y--) {
                                echo "<option value='$y'>$y</option>";
                            }
                        @endphp
                    </select>
                </div>
                <div class="flex items-end">
                    <button onclick="loadReports()" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 h-10">
                        📊 Raporu Yükle
                    </button>
                </div>
            </div>
        </div>

        <!-- Marka Bazında Rapor -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold">🏢 Marka Bazında Kazanılan İşler</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="marka" data-type="text">
                                Marka <span class="sort-icon"></span>
                            </th>
                            <th class="sortable px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase" data-column="adet" data-type="number">
                                İş Adedi <span class="sort-icon"></span>
                            </th>
                            <th class="sortable px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase" data-column="teklif" data-type="number">
                                Toplam Teklif <span class="sort-icon"></span>
                            </th>
                            <th class="sortable px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase" data-column="alis" data-type="number">
                                Toplam Alış <span class="sort-icon"></span>
                            </th>
                            <th class="sortable px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase" data-column="kar" data-type="number">
                                Toplam Kar <span class="sort-icon"></span>
                            </th>
                            <th class="sortable px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase" data-column="kar_orani" data-type="number">
                                Kar Oranı <span class="sort-icon"></span>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="marka-rapor-body" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                Rapor yüklemek için yukarıdan yıl seçin ve "Raporu Yükle" butonuna tıklayın.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Müşteri Bazında Rapor -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold">👥 Müşteri Bazında Kazanılan İşler ve Verimlilik</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="musteri" data-type="text">
                                Müşteri <span class="sort-icon"></span>
                            </th>
                            <th class="sortable px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase" data-column="adet" data-type="number">
                                İş Adedi <span class="sort-icon"></span>
                            </th>
                            <th class="sortable px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase" data-column="ziyaret" data-type="number">
                                Ziyaret Adedi <span class="sort-icon"></span>
                            </th>
                            <th class="sortable px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase" data-column="arama" data-type="number">
                                Arama Adedi <span class="sort-icon"></span>
                            </th>
                            <th class="sortable px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase" data-column="teklif" data-type="number">
                                Toplam Teklif <span class="sort-icon"></span>
                            </th>
                            <th class="sortable px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase" data-column="kar" data-type="number">
                                Toplam Kar <span class="sort-icon"></span>
                            </th>
                            <th class="sortable px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase" data-column="kar_orani" data-type="number">
                                Kar Oranı <span class="sort-icon"></span>
                            </th>
                            <th class="sortable px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase" data-column="is_ziyaret" data-type="number">
                                İş/Ziyaret <span class="sort-icon"></span>
                            </th>
                            <th class="sortable px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase" data-column="kar_ziyaret" data-type="number">
                                Kar/Ziyaret <span class="sort-icon"></span>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="musteri-rapor-body" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                Rapor yüklemek için yukarıdan yıl seçin ve "Raporu Yükle" butonuna tıklayın.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function loadReports() {
            const yil = $('#yil-secim').val();
            
            // Marka bazında rapor
            $.ajax({
                url: '/api/rapor-marka',
                method: 'POST',
                data: { yil: yil },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(data) {
                    let html = '';
                    let toplamAdet = 0;
                    let toplamTeklif = 0;
                    let toplamAlis = 0;
                    let toplamKar = 0;
                    
                    if(data.length === 0) {
                        html = '<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Bu yıl için veri bulunamadı.</td></tr>';
                    } else {
                        data.forEach(item => {
                            const karOrani = item.toplam_teklif > 0 ? ((item.toplam_kar / item.toplam_teklif) * 100).toFixed(1) : 0;
                            
                            html += `
                                <tr class="hover:bg-gray-50" 
                                    data-marka="${item.marka || '-'}"
                                    data-adet="${item.adet}"
                                    data-teklif="${parseFloat(item.toplam_teklif)}"
                                    data-alis="${parseFloat(item.toplam_alis)}"
                                    data-kar="${parseFloat(item.toplam_kar)}"
                                    data-kar_orani="${parseFloat(karOrani)}">
                                    <td class="px-6 py-4 font-medium">${item.marka || '-'}</td>
                                    <td class="px-6 py-4 text-right font-semibold">${item.adet}</td>
                                    <td class="px-6 py-4 text-right">$${parseFloat(item.toplam_teklif).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                    <td class="px-6 py-4 text-right">$${parseFloat(item.toplam_alis).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                    <td class="px-6 py-4 text-right font-semibold text-green-600">$${parseFloat(item.toplam_kar).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="px-3 py-1 rounded-full text-sm font-semibold ${karOrani >= 30 ? 'bg-green-100 text-green-800' : karOrani >= 15 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}">
                                            %${karOrani}
                                        </span>
                                    </td>
                                </tr>
                            `;
                            
                            toplamAdet += parseInt(item.adet);
                            toplamTeklif += parseFloat(item.toplam_teklif);
                            toplamAlis += parseFloat(item.toplam_alis);
                            toplamKar += parseFloat(item.toplam_kar);
                        });
                        
                        const toplamKarOrani = toplamTeklif > 0 ? ((toplamKar / toplamTeklif) * 100).toFixed(1) : 0;
                        
                        html += `
                            <tr class="bg-blue-50 font-bold">
                                <td class="px-6 py-4">TOPLAM</td>
                                <td class="px-6 py-4 text-right">${toplamAdet}</td>
                                <td class="px-6 py-4 text-right">$${toplamTeklif.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                <td class="px-6 py-4 text-right">$${toplamAlis.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                <td class="px-6 py-4 text-right text-green-600">$${toplamKar.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                <td class="px-6 py-4 text-right">
                                    <span class="px-3 py-1 rounded-full text-sm ${toplamKarOrani >= 30 ? 'bg-green-100 text-green-800' : toplamKarOrani >= 15 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}">
                                        %${toplamKarOrani}
                                    </span>
                                </td>
                            </tr>
                        `;
                    }
                    
                    $('#marka-rapor-body').html(html);
                },
                error: function() {
                    alert('Marka raporu yüklenirken hata oluştu!');
                }
            });
            
            // Müşteri bazında rapor
            $.ajax({
                url: '/api/rapor-musteri',
                method: 'POST',
                data: { yil: yil },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(data) {
                    let html = '';
                    let toplamAdet = 0;
                    let toplamZiyaret = 0;
                    let toplamArama = 0;
                    let toplamTeklif = 0;
                    let toplamKar = 0;
                    
                    if(data.length === 0) {
                        html = '<tr><td colspan="9" class="px-6 py-8 text-center text-gray-500">Bu yıl için veri bulunamadı.</td></tr>';
                    } else {
                        data.forEach(item => {
                            const karOrani = item.toplam_teklif > 0 ? ((item.toplam_kar / item.toplam_teklif) * 100).toFixed(1) : 0;
                            const isZiyaret = item.ziyaret_adedi > 0 ? (item.adet / item.ziyaret_adedi).toFixed(2) : 0;
                            const karZiyaret = item.ziyaret_adedi > 0 ? (item.toplam_kar / item.ziyaret_adedi).toFixed(2) : 0;
                            
                            // Verimlilik renklendirmesi
                            let verimlilikClass = 'bg-gray-100 text-gray-800';
                            if(item.ziyaret_adedi > 0) {
                                if(karZiyaret >= 1000) {
                                    verimlilikClass = 'bg-green-100 text-green-800';
                                } else if(karZiyaret >= 500) {
                                    verimlilikClass = 'bg-yellow-100 text-yellow-800';
                                } else if(karZiyaret > 0) {
                                    verimlilikClass = 'bg-orange-100 text-orange-800';
                                } else {
                                    verimlilikClass = 'bg-red-100 text-red-800';
                                }
                            }
                            
                            html += `
                                <tr class="hover:bg-gray-50"
                                    data-musteri="${item.musteri || '-'}"
                                    data-adet="${item.adet}"
                                    data-ziyaret="${item.ziyaret_adedi}"
                                    data-arama="${item.arama_adedi}"
                                    data-teklif="${parseFloat(item.toplam_teklif)}"
                                    data-kar="${parseFloat(item.toplam_kar)}"
                                    data-kar_orani="${parseFloat(karOrani)}"
                                    data-is_ziyaret="${parseFloat(isZiyaret)}"
                                    data-kar_ziyaret="${parseFloat(karZiyaret)}">
                                    <td class="px-6 py-4 font-medium">
                                        <a href="/musteriler/${item.musteri_id}" class="text-blue-600 hover:underline">
                                            ${item.musteri || '-'}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-right font-semibold">${item.adet}</td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-800">
                                            ${item.ziyaret_adedi}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">
                                            ${item.arama_adedi}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">$${parseFloat(item.toplam_teklif).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                    <td class="px-6 py-4 text-right font-semibold text-green-600">$${parseFloat(item.toplam_kar).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="px-3 py-1 rounded-full text-sm font-semibold ${karOrani >= 30 ? 'bg-green-100 text-green-800' : karOrani >= 15 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}">
                                            %${karOrani}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-sm font-medium">${isZiyaret}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold ${verimlilikClass}">
                                            $${parseFloat(karZiyaret).toLocaleString('en-US', {minimumFractionDigits: 2})}
                                        </span>
                                    </td>
                                </tr>
                            `;
                            
                            toplamAdet += parseInt(item.adet);
                            toplamZiyaret += parseInt(item.ziyaret_adedi);
                            toplamArama += parseInt(item.arama_adedi || 0);
                            toplamTeklif += parseFloat(item.toplam_teklif);
                            toplamKar += parseFloat(item.toplam_kar);
                        });
                        
                        const toplamKarOrani = toplamTeklif > 0 ? ((toplamKar / toplamTeklif) * 100).toFixed(1) : 0;
                        const toplamIsZiyaret = toplamZiyaret > 0 ? (toplamAdet / toplamZiyaret).toFixed(2) : 0;
                        const toplamKarZiyaret = toplamZiyaret > 0 ? (toplamKar / toplamZiyaret).toFixed(2) : 0;
                        
                        html += `
                            <tr class="bg-blue-50 font-bold">
                                <td class="px-6 py-4">TOPLAM</td>
                                <td class="px-6 py-4 text-right">${toplamAdet}</td>
                                <td class="px-6 py-4 text-right">${toplamZiyaret}</td>
                                <td class="px-6 py-4 text-right">${toplamArama}</td>
                                <td class="px-6 py-4 text-right">$${toplamTeklif.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                <td class="px-6 py-4 text-right text-green-600">$${toplamKar.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                <td class="px-6 py-4 text-right">
                                    <span class="px-3 py-1 rounded-full text-sm ${toplamKarOrani >= 30 ? 'bg-green-100 text-green-800' : toplamKarOrani >= 15 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}">
                                        %${toplamKarOrani}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">${toplamIsZiyaret}</td>
                                <td class="px-6 py-4 text-right">$${parseFloat(toplamKarZiyaret).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                            </tr>
                        `;
                    }
                    
                    $('#musteri-rapor-body').html(html);
                },
                error: function() {
                    alert('Müşteri raporu yüklenirken hata oluştu!');
                }
            });
        }
        
        // Sayfa yüklendiğinde mevcut yıl için raporu yükle
        $(document).ready(function() {
            loadReports();
            
            // Sıralama fonksiyonu
            let sortDirections = {};
            
            $('.sortable').click(function() {
                const column = $(this).data('column');
                const type = $(this).data('type');
                const table = $(this).closest('table');
                const tbody = table.find('tbody');
                const rows = tbody.find('tr:not(.bg-blue-50)').toArray();
                
                // Sıralama yönünü belirle
                if (!sortDirections[column]) {
                    sortDirections[column] = 'asc';
                } else {
                    sortDirections[column] = sortDirections[column] === 'asc' ? 'desc' : 'asc';
                }
                
                const isAsc = sortDirections[column] === 'asc';
                
                // İkonları güncelle
                table.find('.sort-icon').text('');
                $(this).find('.sort-icon').text(isAsc ? ' ▲' : ' ▼');
                
                // Satırları sırala
                rows.sort(function(a, b) {
                    let aVal = $(a).data(column);
                    let bVal = $(b).data(column);
                    
                    if (type === 'number') {
                        aVal = parseFloat(aVal) || 0;
                        bVal = parseFloat(bVal) || 0;
                        return isAsc ? aVal - bVal : bVal - aVal;
                    } else {
                        // Text için
                        aVal = String(aVal || '').toLowerCase();
                        bVal = String(bVal || '').toLowerCase();
                        if (aVal < bVal) return isAsc ? -1 : 1;
                        if (aVal > bVal) return isAsc ? 1 : -1;
                        return 0;
                    }
                });
                
                // Toplam satırını bul
                const totalRow = tbody.find('tr.bg-blue-50');
                
                // Sıralanmış satırları ekle
                tbody.empty();
                $.each(rows, function(index, row) {
                    tbody.append(row);
                });
                
                // Toplam satırını en sona ekle
                if (totalRow.length > 0) {
                    tbody.append(totalRow);
                }
            });
        });
    </script>
</body>
</html>
