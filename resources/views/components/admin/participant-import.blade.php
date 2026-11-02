<form
    class="participant-import-form"
    method="POST"
    action="{{ route('participants.import') }}"
    enctype="multipart/form-data"
>
    @csrf
    <label class="participant-file-picker">
        <span>Pilih file Excel</span>
        <input type="file" name="file" accept=".xlsx,.xls,.csv" required>
    </label>
    <button class="participant-secondary-button" type="submit">Impor Excel</button>
</form>
