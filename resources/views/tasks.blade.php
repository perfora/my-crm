<x-task-form />

<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Görevler</h2>
    
    @php
        $tasks = \App\Models\Task::latest()->get();
    @endphp
    
    @forelse($tasks as $task)
        <div class="border p-4 mb-2 rounded">
            <h3 class="font-bold">{{ $task->title }}</h3>
            <p class="text-gray-600">{{ $task->description }}</p>
            <p class="text-sm text-gray-500">Tarih: {{ $task->due_date }}</p>
        </div>
    @empty
        <p class="text-gray-500">Henüz görev yok.</p>
    @endforelse
</div>