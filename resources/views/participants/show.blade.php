<x-admin.layout title="Detail Peserta">
    <div class="participants-content">
        <header class="dashboard-header">
            <div>
                <h1>Detail Peserta</h1>
                <p>Informasi lengkap peserta.</p>
            </div>
            <a class="participant-primary-button" href="{{ route('participants.edit', $peserta) }}">Ubah Peserta</a>
        </header>

        <x-admin.panel title="{{ $peserta->nama }}" class="participant-detail-panel">
            <dl class="participant-detail-list">
                <div><dt>Email</dt><dd>{{ $peserta->email }}</dd></div>
                <div><dt>Asal Sekolah/Universitas</dt><dd>{{ $peserta->asal_instansi ?: '—' }}</dd></div>
                <div><dt>Jumlah sertifikat</dt><dd>{{ number_format($peserta->sertifikat_count, 0, ',', '.') }}</dd></div>
                <div><dt>Ditambahkan</dt><dd>{{ $peserta->dibuat_pada?->format('d M Y, H:i') ?? '—' }}</dd></div>
            </dl>
            <a class="participant-action participant-back-link" href="{{ route('participants.index') }}">Kembali ke Data Peserta</a>
        </x-admin.panel>
    </div>
</x-admin.layout>
