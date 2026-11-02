<dialog
    class="participant-modal certificate-type-modal"
    aria-labelledby="certificate-type-modal-title"
    data-certificate-type-modal
    data-modal-kind="{{ $editJenis ? 'edit' : 'create' }}"
    data-store-url="{{ route('certificate-types.store') }}"
    @if ($shouldOpen) data-open-on-load @endif
>
    <div class="participant-modal-content">
        <button class="certificate-type-modal-close" type="button" aria-label="Tutup form" data-close-participant-modal>
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>
        <header class="participant-modal-header">
            <h2 id="certificate-type-modal-title">{{ $editJenis ? 'Ubah Jenis Sertifikat' : 'Tambah Jenis Sertifikat' }}</h2>
            <p>{{ $editJenis ? 'Perbarui informasi jenis sertifikat.' : 'Isi informasi jenis sertifikat baru.' }}</p>
        </header>

        <x-admin.certificate-type-form
            :jenis="$editJenis"
            :action="$editJenis ? route('certificate-types.update', $editJenis) : route('certificate-types.store')"
            :method="$editJenis ? 'PUT' : 'POST'"
            :submit-label="$editJenis ? 'Simpan Perubahan' : 'Simpan'"
            :id-prefix="$editJenis ? 'certificate-type-edit-' : 'certificate-type-create-'"
            :modal="true"
        />
    </div>
</dialog>
