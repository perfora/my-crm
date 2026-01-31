<div class="bg-white rounded-lg shadow {{ $class ?? '' }}">
    @if(isset($title))
    <div class="p-6 border-b flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800">{{ $title }}</h2>
        @if(isset($action))
            {{ $action }}
        @endif
    </div>
    @endif
    
    <div class="{{ isset($noPadding) ? '' : 'p-6' }}">
        {{ $slot }}
    </div>
</div>
