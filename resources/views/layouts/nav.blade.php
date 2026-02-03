<nav class="bg-white shadow-lg mb-6 sticky top-0 z-50">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center space-x-8">
                <a href="/" class="text-xl font-bold text-gray-800">CRM</a>
                
                <div class="hidden md:flex space-x-4">
                    <a href="/" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('/') ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        Ana Sayfa
                    </a>
                    <a href="/tum-isler" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('tum-isler*') ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        Tüm İşler
                    </a>
                    <a href="/musteriler" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('musteriler*') ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        Firmalar
                    </a>
                    <a href="/markalar" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('markalar*') ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        Markalar
                    </a>
                    <a href="/kisiler" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('kisiler*') ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        Kişiler
                    </a>
                    <a href="/urunler" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('urunler*') ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        📦 Ürünler
                    </a>
                    <a href="/tedarikci-fiyatlari" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('tedarikci-fiyatlari*') ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        💰 Fiyatlar
                    </a>
                    <a href="/fiyat-teklifleri" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('fiyat-teklifleri*') ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        📄 Teklifler
                    </a>
                    <a href="/teklif-kosullari" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('teklif-kosullari*') ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        📋 Teklif Koşulları
                    </a>
                    <a href="/ziyaretler" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('ziyaretler*') ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        Ziyaretler
                    </a>
                    <a href="/raporlar" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('raporlar*') ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                        📊 Raporlar
                    </a>
                    <a href="/notion-settings" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->is('notion-settings*') ? 'bg-purple-500 text-white' : 'text-purple-700 hover:bg-purple-100' }}">
                        🔗 Notion
                    </a>
                </div>
            </div>
            
            <div class="hidden md:flex items-center">
                <span class="text-sm text-gray-600">{{ date('d.m.Y') }}</span>
            </div>
        </div>
    </div>
</nav>

<!-- Scroll to Top/Bottom Button -->
<button id="scrollBtn" class="fixed right-6 bottom-6 bg-blue-500 hover:bg-blue-600 text-white p-3 rounded-full shadow-lg transition-all duration-300 opacity-0 pointer-events-none z-40">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
    </svg>
</button>

<script>
    (function() {
        const scrollBtn = document.getElementById('scrollBtn');
        const scrollIcon = scrollBtn.querySelector('svg path');
        let isAtBottom = false;
        
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const scrollHeight = document.documentElement.scrollHeight;
            const clientHeight = document.documentElement.clientHeight;
            
            // Sayfanın %80'inden fazla scroll edilmişse alt kısmındayız
            isAtBottom = (scrollTop + clientHeight) >= (scrollHeight * 0.8);
            
            // 300px'den fazla scroll edildiyse butonu göster
            if (scrollTop > 300) {
                scrollBtn.classList.remove('opacity-0', 'pointer-events-none');
                scrollBtn.classList.add('opacity-100');
            } else {
                scrollBtn.classList.add('opacity-0', 'pointer-events-none');
                scrollBtn.classList.remove('opacity-100');
            }
            
            // İkon yönünü değiştir
            if (isAtBottom) {
                scrollIcon.setAttribute('d', 'M5 10l7-7m0 0l7 7m-7-7v18'); // Yukarı ok
            } else {
                scrollIcon.setAttribute('d', 'M19 14l-7 7m0 0l-7-7m7 7V3'); // Aşağı ok
            }
        });
        
        scrollBtn.addEventListener('click', function() {
            if (isAtBottom) {
                // Yukarı git
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                // Aşağı git
                window.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'smooth' });
            }
        });
    })();
</script>
