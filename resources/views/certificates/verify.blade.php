<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#cce8ec">
    <title>Verifikasi Sertifikat - {{ config('app.name', 'Sukarobot Academy') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="certificate-verification-page">
    <header class="certificate-verification-header">
        <img src="{{ asset('images/logo.png') }}" alt="Sukarobot Academy">
        <strong>Verifikasi Sertifikat</strong>
    </header>

    <main class="certificate-verification-main">
        <section
            class="certificate-verification-result {{ $sertifikat->isAktif() ? 'is-valid' : 'is-cancelled' }}"
            aria-live="polite"
        >
            <span class="certificate-verification-result-icon" aria-hidden="true">
                @if ($sertifikat->isAktif())
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="m6 12.5 4 4L18.5 8" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                @else
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="m7 7 10 10M17 7 7 17" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                @endif
            </span>
            <div>
                <h1>{{ $sertifikat->isAktif() ? 'Sertifikat ditemukan' : 'Sertifikat tidak berlaku' }}</h1>
                <p>Status sertifikat: {{ $sertifikat->isAktif() ? 'Aktif' : 'Dibatalkan' }}</p>
            </div>
        </section>

        <div class="certificate-verification-content">
            <section class="certificate-verification-details" aria-label="Informasi sertifikat">
                <dl>
                    <div><dt>Nama</dt><dd>{{ $sertifikat->peserta->nama }}</dd></div>
                    <div><dt>Jenis</dt><dd>{{ $sertifikat->jenis->nama_jenis }}</dd></div>
                    <div><dt>Nomor</dt><dd>{{ $sertifikat->nomor_sertifikat }}</dd></div>
                    <div><dt>Tanggal terbit</dt><dd>{{ $sertifikat->tanggal_terbit->locale('id')->translatedFormat('j M Y') }}</dd></div>
                </dl>

                @if ($hasPdf)
                    <a class="certificate-verification-download" href="{{ route('certificates.verify.download', $sertifikat->token_publik) }}">
                        Unduh PDF
                    </a>
                @elseif ($sertifikat->isAktif())
                    <p class="certificate-verification-note">Berkas PDF sertifikat belum tersedia.</p>
                @endif
                @if ($hasPdf)
                    <p class="certificate-verification-note">
                    Keaslian sertifikat dapat diperiksa dengan memindai QR code pada sertifikat.
                    </p>
                @endif
            </section>

            @if ($hasPdf)
                <section class="certificate-verification-preview" aria-label="Pratinjau sertifikat">
                    <iframe
                        src="{{ route('certificates.verify.pdf', $sertifikat->token_publik) }}"
                        title="Pratinjau PDF sertifikat {{ $sertifikat->nomor_sertifikat }}"
                    ></iframe>
                </section>
            @endif
        </div>
    </main>

    <footer class="certificate-verification-footer">
        Butuh bantuan? Hubungi Sukarobot Academy. © {{ now()->year }} Sukarobot Academy
    </footer>
</body>
</html>
