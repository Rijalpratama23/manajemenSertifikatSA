@props([
    'jenis' => null,
    'action',
    'method' => 'POST',
    'submitLabel' => 'Simpan',
    'idPrefix' => '',
    'modal' => false,
])

@php($codeLocked = $jenis?->sertifikat()->exists() ?? false)
@php($activeValue = (string) old('aktif', $jenis ? ($jenis->aktif ? '1' : '0') : '1'))

<form class="participant-form certificate-type-form {{ $modal ? 'participant-modal-form' : '' }}" method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <x-forms.field
        :id="$idPrefix . 'kode'"
        name="kode"
        label="Kode *"
        placeholder="Contoh: MAG"
        :value="$codeLocked ? $jenis->kode : old('kode', $jenis?->kode)"
        minlength="2"
        :maxlength="10"
        hint="2 sampai 10 huruf atau angka. Kode dipakai pada nomor sertifikat dan tidak dapat diubah setelah jenis dipakai."
        :readonly="$codeLocked"
        data-certificate-type-code
        data-code-hint
    />

    <x-forms.field
        :id="$idPrefix . 'nama_jenis'"
        name="nama_jenis"
        label="Nama Jenis *"
        placeholder="Contoh: Magang"
        :value="old('nama_jenis', $jenis?->nama_jenis)"
        :maxlength="100"
        hint="Wajib diisi • Maks. 100 karakter"
        data-certificate-type-name
    >
        <x-slot:append>
            <span class="certificate-type-character-count" aria-live="polite"><span data-name-character-count>0</span>/100</span>
        </x-slot:append>
    </x-forms.field>

    <div class="certificate-type-status-field" data-certificate-type-status-select>
        <label class="certificate-type-status-label" for="{{ $idPrefix }}aktif">Status</label>
        <input id="{{ $idPrefix }}aktif" type="hidden" name="aktif" value="{{ $activeValue }}">
        <button
            class="certificate-type-status-trigger"
            type="button"
            aria-expanded="false"
            aria-haspopup="listbox"
            data-status-trigger
        >
            <span data-status-value>{{ $activeValue === '1' ? 'Aktif' : 'Nonaktif' }}</span>
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="m7 10 5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        <div class="certificate-type-status-menu" role="listbox" aria-label="Pilih status" data-status-menu hidden>
            <button type="button" role="option" aria-selected="{{ $activeValue === '1' ? 'true' : 'false' }}" data-status-option="1">Aktif</button>
            <button type="button" role="option" aria-selected="{{ $activeValue === '0' ? 'true' : 'false' }}" data-status-option="0">Nonaktif</button>
        </div>
        @error('aktif')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="participant-form-actions participant-modal-actions">
        <button class="participant-modal-cancel" type="button" data-close-participant-modal>Batal</button>
        <button class="participant-primary-button" type="submit">{{ $submitLabel }}</button>
    </div>
</form>
