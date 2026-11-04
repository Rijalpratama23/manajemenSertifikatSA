<x-admin.layout title="Terbitkan Sertifikat">
    <div class="certificate-issuance-page">
        @if ($issuedCertificate)
            <header class="dashboard-header certificate-issuance-heading">
                <div>
                    <h1>Terbitkan Sertifikat</h1>
                    <p>Buat sertifikat baru untuk peserta. Nomor, QR Code, dan PDF dibuat otomatis</p>
                </div>
            </header>

            <div class="certificate-issued-success" role="status">
                <span class="certificate-issued-success-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="m6 12.5 4 4 8-9" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div>
                    <strong>Sertifikat berhasil diterbitkan</strong>
                    <span>Sertifikat atas nama {{ $issuedCertificate->peserta->nama }} berhasil disimpan.</span>
                </div>
            </div>

            <div class="certificate-issued-columns">
                <section class="certificate-issued-details-panel" aria-label="Informasi sertifikat">
                    <div class="certificate-issued-number">
                        <div>
                            <span>Nomor sertifikat</span>
                            <strong>{{ $issuedCertificate->nomor_sertifikat }}</strong>
                        </div>
                        <button
                            class="certificate-issued-copy-button"
                            type="button"
                            data-copy-certificate-number="{{ $issuedCertificate->nomor_sertifikat }}"
                        >Salin nomor</button>
                    </div>
                    <dl class="certificate-issued-details">
                        <div><dt>Peserta</dt><dd>{{ $issuedCertificate->peserta->nama }}</dd></div>
                        <div><dt>Jenis</dt><dd>{{ $issuedCertificate->jenis->nama_jenis }}</dd></div>
                        <div>
                            <dt>Periode</dt>
                            <dd>{{ $issuedCertificate->periode_mulai->locale('id')->translatedFormat('j M Y') }} - {{ $issuedCertificate->periode_selesai->locale('id')->translatedFormat('j M Y') }}</dd>
                        </div>
                        <div><dt>Tanggal terbit</dt><dd>{{ $issuedCertificate->tanggal_terbit->locale('id')->translatedFormat('j M Y') }}</dd></div>
                        <div>
                            <dt>Status</dt>
                            <dd class="certificate-issued-status">{{ $issuedCertificate->status === \App\Models\Sertifikat::STATUS_AKTIF ? 'Aktif' : 'Dibatalkan' }}</dd>
                        </div>
                    </dl>
                    <p class="certificate-issued-note">
                        Sampaikan nomor sertifikat kepada peserta. Peserta dapat mengunduh sertifikat dengan memasukkan nomor ini dan email terdaftar.
                    </p>
                    <div class="certificate-issued-actions">
                        <a class="certificate-issued-download" href="{{ route('certificate-issues.download', $issuedCertificate) }}">Unduh PDF</a>
                        <a class="certificate-issued-history" href="{{ route('certificate-archive.show', $issuedCertificate) }}">Lihat di Arsip</a>
                    </div>
                    <a class="certificate-issued-another" href="{{ route('certificate-issues.create') }}">Terbitkan untuk peserta lain</a>
                </section>

                <section class="certificate-issued-preview-panel" aria-labelledby="issued-preview-title">
                    <h2 id="issued-preview-title">Hasil PDF</h2>
                    <div class="certificate-issued-pdf-frame">
                        <iframe
                            src="{{ route('certificate-issues.preview', $issuedCertificate) }}#toolbar=0&navpanes=0&scrollbar=0"
                            title="Pratinjau PDF sertifikat {{ $issuedCertificate->nomor_sertifikat }}"
                        ></iframe>
                    </div>
                    <p>Pratinjau sertifikat yang telah diterbitkan.</p>
                </section>
            </div>
        @else
            <x-admin.flash-message />
            <header class="dashboard-header certificate-issuance-heading">
                <div>
                    <h1>Terbitkan Sertifikat</h1>
                    <p>Buat sertifikat baru untuk peserta. Nomor, QR Code, dan PDF dibuat otomatis</p>
                </div>
            </header>

            @if ($reissueCertificate)
                <div class="certificate-archive-reissue-notice" role="status">
                    Penerbitan ulang dari sertifikat <strong>{{ $reissueCertificate->nomor_sertifikat }}</strong>.
                    Tanggal terbit baru menggunakan tanggal hari ini dan nomor sertifikat akan dibuat ulang.
                </div>
            @endif

            @if ($jenisSertifikat->isEmpty())
                <div class="certificate-issuance-empty">
                    <strong>Belum ada template sertifikat aktif</strong>
                    <p>Aktifkan jenis sertifikat dan tambahkan template aktif sebelum menerbitkan sertifikat.</p>
                    <a class="participant-primary-button" href="{{ route('certificate-templates.index') }}">Kelola Template</a>
                </div>
            @else
                @if ($peserta->isEmpty())
                    <div class="admin-alert admin-alert-error" role="alert">
                        Belum ada data peserta. Tambahkan peserta terlebih dahulu sebelum menerbitkan sertifikat.
                    </div>
                @endif
                <form
                    class="certificate-issuance-form"
                    method="POST"
                    action="{{ route('certificate-issues.store') }}"
                    data-certificate-issuance-form
                >
                    @csrf
                    <div class="certificate-issuance-columns">
                        <div class="certificate-issuance-form-panel">
                            <x-admin.certificate-issuance.participant-step
                                :peserta="$peserta"
                                :reissue-certificate="$reissueCertificate"
                            />
                            <x-admin.certificate-issuance.details-step
                                :jenis-sertifikat="$jenisSertifikat"
                                :reissue-certificate="$reissueCertificate"
                            />
                        </div>
                        <aside class="certificate-issuance-aside">
                            <x-admin.certificate-issuance.preview />
                            <div class="certificate-issuance-notice">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/>
                                    <path d="M12 11v5m0-8h.01" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                                <p>Nomor sertifikat dibuat otomatis dan <strong>tidak dapat diubah</strong> setelah terbit. Periksa pratinjau sebelum menerbitkan.</p>
                            </div>
                        </aside>
                    </div>
                    <div class="certificate-issuance-actions">
                        <a class="certificate-issuance-cancel" href="{{ route('dashboard') }}">Batal</a>
                        <button class="participant-primary-button" type="submit" @disabled($peserta->isEmpty())>Terbitkan Sertifikat</button>
                    </div>
                </form>

                <dialog
                    class="certificate-issuance-confirmation"
                    aria-labelledby="certificate-issuance-confirmation-title"
                    aria-describedby="certificate-issuance-confirmation-message"
                    data-certificate-issuance-confirmation
                >
                    <div class="certificate-issuance-confirmation-content">
                        <span class="certificate-issuance-confirmation-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M7 8V4h10v4M7 17H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2M7 14h10v6H7z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M17 11h.01" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <h2 id="certificate-issuance-confirmation-title">Terbitkan sertifikat?</h2>
                        <dl class="certificate-issuance-confirmation-details">
                            <div>
                                <dt>Peserta</dt>
                                <dd data-issuance-confirm-participant>-</dd>
                            </div>
                            <div>
                                <dt>Jenis</dt>
                                <dd data-issuance-confirm-type>-</dd>
                            </div>
                            <div>
                                <dt>Periode</dt>
                                <dd data-issuance-confirm-period>-</dd>
                            </div>
                            <div>
                                <dt>Template</dt>
                                <dd data-issuance-confirm-template>-</dd>
                            </div>
                        </dl>
                        <p id="certificate-issuance-confirmation-message">
                            Nomor sertifikat dibuat otomatis dan <strong>tidak dapat diubah</strong> setelah terbit. Pastikan data sudah benar.
                        </p>
                        <div class="certificate-issuance-confirmation-actions">
                            <button class="certificate-issuance-confirmation-review" type="button" data-issuance-confirm-cancel>Periksa lagi</button>
                            <button class="certificate-issuance-confirmation-submit" type="button" data-issuance-confirm-submit>Terbitkan</button>
                        </div>
                    </div>
                </dialog>
            @endif
        @endif
    </div>
</x-admin.layout>
