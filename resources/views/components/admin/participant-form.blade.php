@props([
    'participant' => null,
    'action',
    'submitLabel',
    'cancelUrl',
    'method' => 'POST',
    'modal' => false,
    'idPrefix' => '',
])

<form class="participant-form {{ $modal ? 'participant-modal-form' : '' }}" method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <x-forms.field
        id="nama"
        name="nama"
        label="Nama peserta"
        placeholder="Masukkan nama lengkap"
        :value="old('nama', $participant?->nama)"
        :maxlength="150"
        hint="Wajib diisi • Maks. 150 karakter"
        :id-prefix="$idPrefix"
    />

    <x-forms.field
        id="email"
        name="email"
        label="Email"
        type="email"
        placeholder="nama@email.com"
        :value="old('email', $participant?->email)"
        :maxlength="150"
        hint="Wajib diisi • Format email yang benar • Maks. 150 karakter"
        :id-prefix="$idPrefix"
    />

    <x-forms.field
        id="asal_instansi"
        name="asal_instansi"
        label="Asal Sekolah/Universitas"
        placeholder="Masukkan nama sekolah/universitas (opsional)"
        :value="old('asal_instansi', $participant?->asal_instansi)"
        :maxlength="150"
        :required="false"
        hint="Opsional • Maks. 150 karakter"
        :id-prefix="$idPrefix"
    />

    <div class="participant-form-actions {{ $modal ? 'participant-modal-actions' : '' }}">
        @if ($modal)
            <button class="participant-modal-cancel" type="button" data-close-participant-modal>Batal</button>
        @else
            <a class="participant-action" href="{{ $cancelUrl }}">Batal</a>
        @endif
        <button class="participant-primary-button" type="submit">{{ $submitLabel }}</button>
    </div>
</form>
