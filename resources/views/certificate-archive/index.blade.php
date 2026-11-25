<x-admin.layout title="Arsip Sertifikat">
    <div class="certificate-archive-page">
        <x-admin.flash-message />
        <header class="certificate-archive-header">
            <div>
                <h1>Arsip Sertifikat</h1>
                <p>
                    {{ $selectedCertificateType
                        ? 'Sertifikat jenis '.$selectedCertificateType->nama_jenis
                        : 'Pilih folder jenis sertifikat untuk melihat arsip peserta.' }}
                </p>
            </div>
            @if ($selectedCertificateType)
                <a class="certificate-archive-back" href="{{ route('certificate-archive.index') }}">Semua folder</a>
            @endif
        </header>

        @if ($selectedCertificateType)
            <section class="certificate-archive-filter-panel" aria-labelledby="archive-filter-title">
                <div class="certificate-archive-filter-heading">
                    <div>
                        <h2 id="archive-filter-title">Filter arsip {{ $selectedCertificateType->nama_jenis }}</h2>
                        <p>Cari peserta atau atur tanggal dan status sertifikat dalam folder ini.</p>
                    </div>
                </div>
                <x-admin.certificate-archive.filters
                    :search="$search"
                    :years="$years"
                    :selected-type="$selectedType"
                    :status="$status"
                    :date-filter="$dateFilter"
                />
            </section>
        @endif

        <x-admin.certificate-archive.summary-stats
            :total="$totalCertificates"
            :active="$activeCertificates"
            :cancelled="$cancelledCertificates"
        />

        @if ($selectedCertificateType)
            <section class="certificate-archive-panel" aria-label="Daftar sertifikat {{ $selectedCertificateType->nama_jenis }}">
                <x-admin.certificate-archive.table :certificates="$certificates" />
                <footer class="certificate-archive-footer">
                    <p>
                        @if ($certificates->total() > 0)
                            Menampilkan {{ $certificates->firstItem() }}-{{ $certificates->lastItem() }} dari {{ $certificates->total() }} sertifikat
                        @else
                            Menampilkan 0 sertifikat
                        @endif
                    </p>
                    {{ $certificates->onEachSide(1)->links() }}
                </footer>
            </section>
        @else
            <section class="certificate-archive-folders" aria-labelledby="archive-folders-title">
                <div class="certificate-archive-folders-heading">
                    <h2 id="archive-folders-title">Folder jenis sertifikat</h2>
                    <p>{{ $certificateTypes->count() }} jenis sertifikat tersedia. Pilih folder untuk melihat daftar pesertanya.</p>
                </div>
                @if ($certificateTypes->isNotEmpty())
                    <div class="certificate-archive-folder-grid">
                        @foreach ($certificateTypes as $type)
                            <a
                                class="certificate-archive-folder"
                                href="{{ route('certificate-archive.index', ['jenis' => $type->id_jenis]) }}"
                            >
                                <span class="certificate-archive-folder-icon certificate-archive-folder-icon--{{ $loop->index % 4 }}" aria-hidden="true">
                                    <svg viewBox="0 0 48 40" fill="none">
                                        <path d="M4 8.5A2.5 2.5 0 0 1 6.5 6h12l4 4H41a3 3 0 0 1 3 3v18a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3v-22.5Z" fill="currentColor"/>
                                        <path d="M5 13h37l-3.5 15H7.5L5 13Z" fill="white" fill-opacity=".2"/>
                                    </svg>
                                </span>
                                <span class="certificate-archive-folder-arrow" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span class="certificate-archive-folder-content">
                                    <strong>{{ $type->nama_jenis }}</strong>
                                    <span class="certificate-archive-folder-count">
                                        <strong>{{ number_format($type->total_certificates, 0, ',', '.') }}</strong> sertifikat
                                    </span>
                                    <span class="certificate-archive-folder-meta">
                                        <span>{{ number_format($type->active_certificates, 0, ',', '.') }} aktif</span>
                                        <span>{{ number_format($type->cancelled_certificates, 0, ',', '.') }} batal</span>
                                    </span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="certificate-archive-folders-empty">
                        Belum ada jenis sertifikat. Tambahkan jenis sertifikat untuk mulai mengelompokkan arsip.
                    </div>
                @endif
            </section>
        @endif
    </div>
</x-admin.layout>
