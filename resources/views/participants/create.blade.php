<x-admin.layout title="Tambah Peserta">
    <div class="participants-content">
        <x-admin.flash-message />
        <header class="dashboard-header">
            <div>
                <h1>Tambah Peserta</h1>
                <p>Masukkan informasi peserta baru.</p>
            </div>
        </header>
        <x-admin.panel title="Informasi Peserta" class="participant-form-panel">
            <x-admin.participant-form
                :action="route('participants.store')"
                submit-label="Simpan Peserta"
                :cancel-url="route('participants.index')"
            />
        </x-admin.panel>
    </div>
</x-admin.layout>
