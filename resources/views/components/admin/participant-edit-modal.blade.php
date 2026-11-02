<dialog
    class="participant-modal"
    aria-labelledby="participant-edit-modal-title"
    data-edit-participant-modal
    @if ($editParticipant && $errors->hasAny(['nama', 'email', 'asal_instansi'])) data-open-on-load @endif
>
    <div class="participant-modal-content">
        <header class="participant-modal-header">
            <h2 id="participant-edit-modal-title">Ubah Peserta</h2>
            <p>Perbarui data peserta yang dipilih dari daftar.</p>
        </header>

        <x-admin.participant-form
            :participant="$editParticipant"
            :action="$editParticipant ? route('participants.update', $editParticipant) : route('participants.index')"
            :cancel-url="route('participants.index')"
            method="PUT"
            submit-label="Simpan"
            :modal="true"
            id-prefix="edit-"
        />
    </div>
</dialog>
