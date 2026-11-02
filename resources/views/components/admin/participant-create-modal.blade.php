<dialog
    class="participant-modal"
    aria-labelledby="participant-modal-title"
    data-participant-modal
    @if ($errors->hasAny(['nama', 'email', 'asal_instansi'])) data-open-on-load @endif
>
    <div class="participant-modal-content">
        <header class="participant-modal-header">
            <h2 id="participant-modal-title">Tambah Peserta</h2>
            <p>Isi data peserta baru untuk ditambahkan ke daftar.</p>
        </header>

        <x-admin.participant-form
            :action="route('participants.store')"
            :cancel-url="route('participants.index')"
            submit-label="Simpan"
            :modal="true"
        />
    </div>
</dialog>
