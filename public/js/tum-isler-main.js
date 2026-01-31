// Tüm İşler - Ana JavaScript Fonksiyonları

function toggleForm() {
    const form = document.getElementById('is-ekle-form');
    const icon = document.getElementById('form-toggle-icon');
    
    if (form && icon) {
        if (form.style.display === 'none') {
            form.style.display = 'block';
            icon.style.transform = 'rotate(180deg)';
        } else {
            form.style.display = 'none';
            icon.style.transform = 'rotate(0deg)';
        }
    }
}

function toggleFilters() {
    const filters = document.getElementById('filters-form');
    const icon = document.getElementById('filter-toggle-icon');
    
    if (filters && icon) {
        if (filters.style.display === 'none') {
            filters.style.display = 'block';
            icon.style.transform = 'rotate(180deg)';
        } else {
            filters.style.display = 'none';
            icon.style.transform = 'rotate(0deg)';
        }
    }
}

function yenilemeAcTumIsler(isId) {
    if(!confirm('Bu iş için yenileme kaydı açılsın mı?')) {
        return;
    }
    
    const button = event.target;
    button.disabled = true;
    button.innerHTML = '⏳ Açılıyor...';
    
    $.ajax({
        url: '/api/yenileme-ac',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''
        },
        data: { is_id: isId },
        success: function(response) {
            alert('✓ Yenileme kaydı oluşturuldu!\n\nYeni iş: ' + response.yeni_is.name);
            location.reload();
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.message || 'Hata oluştu!';
            alert('❌ ' + error);
            button.disabled = false;
            button.innerHTML = '🔄 Yenile';
        }
    });
}
