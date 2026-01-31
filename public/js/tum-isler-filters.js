// Filtre Kaydetme ve Yükleme Sistemi (Database-backed)
async function saveCurrentFilter() {
    const filterName = document.getElementById('filterName').value.trim();
    if (!filterName) {
        alert('Lütfen filtre adı girin!');
        return;
    }
    
    const formData = {};
    const form = document.getElementById('filterForm');
    const inputs = form.querySelectorAll('input, select');
    
    inputs.forEach(input => {
        if (input.name && input.value) {
            formData[input.name] = input.value;
        }
    });
    
    try {
        const response = await fetch('/api/saved-filters', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                name: filterName,
                page: 'tum-isler',
                filter_data: formData
            })
        });
        
        if (response.ok) {
            alert('✓ Filtre kaydedildi: ' + filterName);
            document.getElementById('filterName').value = '';
            updateFilterButtons();
        } else {
            alert('❌ Kayıt başarısız!');
        }
    } catch (error) {
        console.error('Filter save error:', error);
        alert('❌ Bağlantı hatası!');
    }
}

async function loadFilter(filterName) {
    try {
        const response = await fetch('/api/saved-filters?page=tum-isler');
        const filters = await response.json();
        const filter = filters.find(f => f.name === filterName);
        
        if (!filter) return;
        
        const form = document.getElementById('filterForm');
        Object.keys(filter.filter_data).forEach(key => {
            const input = form.querySelector('[name="' + key + '"]');
            if (input) {
                input.value = filter.filter_data[key];
                if ($(input).hasClass('select2-hidden-accessible')) {
                    $(input).val(filter.filter_data[key]).trigger('change');
                }
            }
        });
        
        form.submit();
    } catch (error) {
        console.error('Filter load error:', error);
    }
}

async function deleteFilter(filterName) {
    if (!confirm('Bu filtreyi silmek istediğinize emin misiniz?\n\n' + filterName)) {
        return;
    }
    
    try {
        const response = await fetch('/api/saved-filters/' + encodeURIComponent(filterName) + '?page=tum-isler', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        if (response.ok) {
            alert('✓ Filtre silindi: ' + filterName);
            updateFilterButtons();
        } else {
            alert('❌ Silme başarısız!');
        }
    } catch (error) {
        console.error('Filter delete error:', error);
        alert('❌ Bağlantı hatası!');
    }
}

async function updateFilterButtons() {
    try {
        const response = await fetch('/api/saved-filters?page=tum-isler');
        const filters = await response.json();
        const container = document.getElementById('savedFiltersButtons');
        
        if (filters.length === 0) {
            container.innerHTML = '<p class="text-sm text-gray-500">Henüz kayıtlı filtre yok</p>';
            return;
        }
        
        let html = '';
        filters.forEach(filter => {
            html += `
                <div class="inline-flex items-center gap-0.5 bg-blue-50 hover:bg-blue-100 rounded border border-blue-200 transition-colors">
                    <button type="button" onclick="loadFilter('${filter.name}'); return false;" class="px-2 py-0.5 text-xs font-medium text-blue-700">
                        ${filter.name}
                    </button>
                    <button type="button" onclick="deleteFilter('${filter.name}'); return false;" class="px-1.5 py-0.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-r text-xs">
                        ×
                    </button>
                </div>
            `;
        });
        
        container.innerHTML = html;
    } catch (error) {
        console.error('Filter buttons update error:', error);
    }
}

// localStorage'dan Database'e otomatik migration
async function migrateFiltersToDatabase() {
    const localFilters = JSON.parse(localStorage.getItem('tumIslerFilters') || '{}');
    
    if (Object.keys(localFilters).length === 0) {
        return;
    }
    
    console.log('📦 localStorage\'da ' + Object.keys(localFilters).length + ' filtre bulundu, database\'e taşınıyor...');
    
    let successCount = 0;
    
    for (const [filterName, filterData] of Object.entries(localFilters)) {
        try {
            const response = await fetch('/api/saved-filters', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    name: filterName,
                    page: 'tum-isler',
                    filter_data: filterData
                })
            });
            
            if (response.ok) {
                successCount++;
                console.log('✓ Taşındı: ' + filterName);
            }
        } catch (error) {
            console.error('Taşıma hatası (' + filterName + '):', error);
        }
    }
    
    if (successCount > 0) {
        localStorage.removeItem('tumIslerFilters');
        console.log('✅ ' + successCount + ' filtre database\'e taşındı!');
        updateFilterButtons();
    }
}
