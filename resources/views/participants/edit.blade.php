<x-admin.layout title="Ubah Peserta">
    <div class="participants-content">
        <x-admin.flash-message />
        <header class="dashboard-header">
            <div>
                <h1>Ubah Peserta</h1>
                <p>Perbarui informasi peserta.</p>
            </div>
        </header>
        <x-admin.panel title="Informasi Peserta" class="participant-form-panel">
            <x-admin.participant-form
                :participant="$participant"
                :action="route('participants.update', $participant)"
                method="PUT"
                submit-label="Simpan Perubahan"
                :cancel-url="route('participants.index')"
            />
        </x-admin.panel>
    </div>
</x-admin.layout>
