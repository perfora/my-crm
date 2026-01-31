@props(['items', 'columns', 'emptyMessage' => 'Kayıt bulunamadı'])

<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                @foreach($columns as $column)
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    {{ $column['label'] }}
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($items as $item)
            <tr>
                @foreach($columns as $column)
                <td class="px-6 py-4 {{ $column['class'] ?? 'whitespace-nowrap' }}">
                    @if(isset($column['format']))
                        {!! $column['format']($item) !!}
                    @else
                        {{ data_get($item, $column['field']) ?? '-' }}
                    @endif
                </td>
                @endforeach
            </tr>
            @empty
            <tr>
                <td colspan="{{ count($columns) }}" class="px-6 py-4 text-center text-gray-500">
                    {{ $emptyMessage }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
