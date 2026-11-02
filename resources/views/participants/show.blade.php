<x-admin.layout title="Detail Peserta">
    <div class="participants-content">
        <header class="dashboard-header">
            <div>
                <h1>Detail Peserta</h1>
                <p>Informasi peserta dan sertifikat yang telah diterbitkan.</p>
            </div>
            <div class="participant-detail-header-actions">
                <a class="participant-secondary-button" href="{{ route('participants.index') }}">Kembali</a>
                <a class="participant-primary-button" href="{{ route('participants.edit', $peserta) }}">Ubah Peserta</a>
            </div>
        </header>

        <x-admin.panel title="Informasi Peserta" class="participant-detail-panel">
            <div class="participant-detail-profile">
                <span class="participant-detail-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($peserta->nama, 0, 2)) }}</span>
                <div>
                    <h2>{{ $peserta->nama }}</h2>
                    <p>{{ $peserta->email }}</p>
                </div>
            </div>
            <dl class="participant-detail-list">
                <div><dt>Asal Sekolah/Universitas</dt><dd>{{ $peserta->asal_instansi ?: '—' }}</dd></div>
                <div><dt>Jumlah sertifikat</dt><dd>{{ number_format($peserta->sertifikat_count, 0, ',', '.') }}</dd></div>
                <div><dt>Ditambahkan</dt><dd>{{ $peserta->dibuat_pada?->format('d M Y, H:i') ?? '—' }}</dd></div>
                <div><dt>Terakhir diperbarui</dt><dd>{{ $peserta->diubah_pada?->format('d M Y, H:i') ?? '—' }}</dd></div>
            </dl>
        </x-admin.panel>

        <x-admin.panel
            title="Sertifikat Peserta"
            :subtitle="number_format($peserta->sertifikat_count, 0, ',', '.') . ' sertifikat'"
            class="participant-certificates-panel"
        >
            <div class="participant-table-scroll">
                <table class="participant-table">
                    <thead>
                        <tr>
                            <th scope="col">No. Sertifikat</th>
                            <th scope="col">Jenis Sertifikat</th>
                            <th scope="col">Tanggal Terbit</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($peserta->sertifikat as $sertifikat)
                            <tr>
                                <td class="participant-name">{{ $sertifikat->nomor_sertifikat }}</td>
                                <td>{{ $sertifikat->jenis?->nama_jenis ?? '—' }}</td>
                                <td class="participant-date">{{ $sertifikat->tanggal_terbit?->format('d M Y') ?? '—' }}</td>
                                <td>
                                    <span class="participant-certificate-status {{ $sertifikat->status === \App\Models\Sertifikat::STATUS_AKTIF ? 'is-active' : 'is-cancelled' }}">
                                        {{ $sertifikat->status === \App\Models\Sertifikat::STATUS_AKTIF ? 'Aktif' : 'Dibatalkan' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="participant-empty" colspan="4">Peserta ini belum memiliki sertifikat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.panel>
    </div>
</x-admin.layout>
