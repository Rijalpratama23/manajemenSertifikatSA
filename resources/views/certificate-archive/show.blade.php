<x-admin.layout title="Detail Sertifikat">
    <div class="certificate-archive-page">
        <x-admin.flash-message />
        <header class="certificate-archive-header certificate-archive-detail-header">
            <div>
                <h1>Detail Sertifikat</h1>
                <p>Informasi sertifikat yang tersimpan di arsip.</p>
            </div>
            <a class="certificate-archive-back" href="{{ route('certificate-archive.index') }}">Kembali ke arsip</a>
        </header>

        <div class="certificate-issued-columns">
            <section class="certificate-issued-details-panel" aria-label="Informasi sertifikat">
                <div class="certificate-issued-number">
                    <div>
                        <span>Nomor sertifikat</span>
                        <strong>{{ $sertifikat->nomor_sertifikat }}</strong>
                    </div>
                </div>
                <dl class="certificate-issued-details">
                    <div><dt>Peserta</dt><dd>{{ $sertifikat->peserta->nama }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $sertifikat->peserta->email }}</dd></div>
                    <div><dt>Asal Sekolah/Universitas</dt><dd>{{ $sertifikat->peserta->asal_instansi ?: '—' }}</dd></div>
                    <div><dt>Jenis</dt><dd>{{ $sertifikat->jenis->nama_jenis }}</dd></div>
                    <div>
                        <dt>Periode</dt>
                        <dd>
                            @if ($sertifikat->periode_mulai && $sertifikat->periode_selesai)
                                {{ $sertifikat->periode_mulai->locale('id')->translatedFormat('j M Y') }} - {{ $sertifikat->periode_selesai->locale('id')->translatedFormat('j M Y') }}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div><dt>Tanggal terbit</dt><dd>{{ $sertifikat->tanggal_terbit->locale('id')->translatedFormat('j M Y') }}</dd></div>
                    <div><dt>Durasi</dt><dd>{{ $sertifikat->durasi ?: '—' }}</dd></div>
                    <div><dt>Nilai</dt><dd>{{ $sertifikat->nilai ?: '—' }}</dd></div>
                    <div><dt>Predikat</dt><dd>{{ $sertifikat->predikat ?: '—' }}</dd></div>
                    <div><dt>Status</dt><dd class="{{ $sertifikat->isAktif() ? 'certificate-issued-status' : 'certificate-archive-cancelled-text' }}">{{ $sertifikat->isAktif() ? 'Aktif' : 'Dibatalkan' }}</dd></div>
                    @if (! $sertifikat->isAktif())
                        <div><dt>Alasan batal</dt><dd>{{ $sertifikat->alasan_batal ?: '—' }}</dd></div>
                    @endif
                </dl>
                <div class="certificate-issued-actions">
                    @if ($sertifikat->berkas_pdf)
                        <a class="certificate-issued-download" href="{{ route('certificate-issues.download', $sertifikat) }}">Unduh PDF</a>
                    @endif
                    <a class="certificate-issued-history" href="{{ route('participants.show', $sertifikat->peserta) }}">Lihat peserta</a>
                    @if ($sertifikat->isAktif())
                        <form
                            method="POST"
                            action="{{ route('certificate-archive.cancel', $sertifikat) }}"
                            data-confirm
                            data-confirm-style="template"
                            data-confirm-title="Batalkan sertifikat ini?"
                            data-confirm-message="Sertifikat {{ $sertifikat->nomor_sertifikat }} akan berubah menjadi tidak aktif. Sertifikat dapat diterbitkan ulang dengan nomor baru."
                            data-confirm-label="Batalkan"
                            data-confirm-tone="danger"
                        >
                            @csrf
                            <button class="certificate-archive-action is-danger" type="submit">Batalkan</button>
                        </form>
                    @else
                        <a class="certificate-archive-action is-reissue" href="{{ route('certificate-issues.create', ['ulangi' => $sertifikat->id_sertifikat]) }}">Terbitkan ulang</a>
                    @endif
                </div>
            </section>

            <section class="certificate-issued-preview-panel" aria-labelledby="archive-preview-title">
                <h2 id="archive-preview-title">Pratinjau PDF</h2>
                @if ($sertifikat->berkas_pdf)
                    <div class="certificate-issued-pdf-frame">
                        <iframe
                            src="{{ route('certificate-issues.preview', $sertifikat) }}#toolbar=0&navpanes=0&scrollbar=0"
                            title="Pratinjau PDF sertifikat {{ $sertifikat->nomor_sertifikat }}"
                        ></iframe>
                    </div>
                @else
                    <p>File PDF untuk sertifikat ini tidak tersedia.</p>
                @endif
            </section>
        </div>
    </div>
</x-admin.layout>
