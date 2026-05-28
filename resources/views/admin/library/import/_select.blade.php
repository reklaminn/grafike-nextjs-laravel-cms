{{-- Kolon seçici: CSV başlıklarından birini seçtirir (boş = eşleme yok) --}}
<select name="{{ $name }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    <option value="">— yok —</option>
    @foreach($headers as $h)
        <option value="{{ $h }}" {{ ($selected ?? '') === $h ? 'selected' : '' }}>{{ $h }}</option>
    @endforeach
</select>
