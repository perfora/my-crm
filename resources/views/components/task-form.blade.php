<div class="p-6">
    <form method="POST" action="/tasks">
        @csrf
        <input type="text" name="title" placeholder="Başlık" class="border p-2 w-full" required />
        
        <textarea name="description" placeholder="Açıklama" class="border p-2 w-full mt-2"></textarea>
        
        <input type="date" name="due_date" class="border p-2 w-full mt-2" />
        
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mt-2">Görev Ekle</button>
    </form>
</div>