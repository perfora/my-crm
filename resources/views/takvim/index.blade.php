<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Takvim - CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    @include('layouts.nav')

    <div class="container mx-auto px-4 py-6 max-w-screen-2xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Takvim</h1>
            <div class="flex items-center gap-3">
                <button id="syncBtn" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium">
                    🔄 Senkron Et
                </button>
                <span class="text-sm text-gray-500">Sonraki 30 gün</span>
            </div>
        </div>

        @if($error)
            <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded mb-4">
                {{ $error }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Konu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Başlangıç</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bitiş</th>
                    </tr>
                </thead>
                <tbody id="calendar-body" class="bg-white divide-y divide-gray-200">
                    @forelse($events as $event)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap font-medium">
                                <button type="button" class="text-left font-medium text-gray-900 hover:text-blue-600 toggle-details" data-target="event-{{ $loop->index }}">
                                    {{ $event['subject'] ?: '-' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $event['start'] ? \Carbon\Carbon::parse($event['start'])->timezone('Europe/Istanbul')->format('d.m.Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $event['end'] ? \Carbon\Carbon::parse($event['end'])->timezone('Europe/Istanbul')->format('d.m.Y H:i') : '-' }}
                            </td>
                        </tr>
                        <tr id="event-{{ $loop->index }}" class="hidden bg-gray-50">
                            <td colspan="3" class="px-6 py-4 text-sm text-gray-700">
                                @php
                                    $body = $event['body'] ?? '';
                                    $escaped = e($body);
                                    $withBreaks = nl2br($escaped);
                                    $linkified = preg_replace('~(https?://[^\s<]+)~i', '<a href="$1" target="_blank" class="text-blue-600 hover:underline">$1</a>', $withBreaks);
                                @endphp
                                {!! $linkified ?: '<span class="text-gray-400">Açıklama yok.</span>' !!}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-6 text-center text-gray-500">Takvim kaydı bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const calendarBody = document.getElementById('calendar-body');
        const syncBtn = document.getElementById('syncBtn');

        function formatDate(iso) {
            if (!iso) return '-';
            const d = new Date(iso);
            const pad = (n) => String(n).padStart(2, '0');
            return `${pad(d.getDate())}.${pad(d.getMonth() + 1)}.${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
        }

        function renderEvents(events) {
            if (!events || events.length === 0) {
                calendarBody.innerHTML = `<tr><td colspan="3" class="px-6 py-6 text-center text-gray-500">Takvim kaydı bulunamadı.</td></tr>`;
                return;
            }

            calendarBody.innerHTML = '';
            events.forEach((event, index) => {
                const subject = event.subject || '-';
                const start = formatDate(event.start);
                const end = formatDate(event.end);
                const body = (event.body || '').trim();
                const safeBody = body
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/\n/g, '<br>');

                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap font-medium">
                        <button type="button" class="text-left font-medium text-gray-900 hover:text-blue-600 toggle-details" data-target="event-${index}">
                            ${subject}
                        </button>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${start}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${end}</td>
                `;
                calendarBody.appendChild(row);

                const detailRow = document.createElement('tr');
                detailRow.id = `event-${index}`;
                detailRow.className = 'hidden bg-gray-50';
                detailRow.innerHTML = `
                    <td colspan="3" class="px-6 py-4 text-sm text-gray-700">
                        ${safeBody || '<span class="text-gray-400">Açıklama yok.</span>'}
                    </td>
                `;
                calendarBody.appendChild(detailRow);
            });

            attachToggleHandlers();
        }

        function attachToggleHandlers() {
            document.querySelectorAll('.toggle-details').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = btn.getAttribute('data-target');
                    const row = document.getElementById(id);
                    if (row) {
                        row.classList.toggle('hidden');
                    }
                });
            });
        }

        if (syncBtn) {
            syncBtn.addEventListener('click', async function() {
                syncBtn.disabled = true;
                const original = syncBtn.textContent;
                syncBtn.textContent = '⏳ Senkron...';
                try {
                    const res = await fetch('/takvim/sync');
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        alert(data.error || 'Senkron hatası oluştu.');
                    } else {
                        renderEvents(data.events || []);
                    }
                } catch (e) {
                    alert('Senkron hatası oluştu.');
                } finally {
                    syncBtn.disabled = false;
                    syncBtn.textContent = original;
                }
            });
        }

        const cleanupBtn = document.getElementById('cleanupBtn');
        const pushCrmBtn = document.getElementById('pushCrmBtn');
        // cleanupBtn / pushCrmBtn intentionally not wired in UI

        attachToggleHandlers();
    </script>
</body>
</html>
