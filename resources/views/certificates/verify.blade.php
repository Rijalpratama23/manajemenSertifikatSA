<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Sertifikat - {{ config('app.name', 'Sukarobot Academy') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="certificate-verification-page">
    <main class="certificate-verification-card">
        <img src="{{ asset('images/logo.png') }}" alt="Sukarobot Academy">
        @if ($sertifikat->isAktif())
            <span class="certificate-verification-status is-valid">Sertifikat valid</span>
            <h1>Sertifikat terverifikasi</h1>
            <p>Data sertifikat ini tercatat dan berstatus aktif.</p>
        @else
            <span class="certificate-verification-status is-cancelled">Sertifikat dibatalkan</span>
            <h1>Sertifikat tidak berlaku</h1>
            <p>Sertifikat ini telah dibatalkan oleh penerbit.</p>
        @endif
        <dl>
            <div><dt>Nomor sertifikat</dt><dd>{{ $sertifikat->nomor_sertifikat }}</dd></div>
            <div><dt>Jenis sertifikat</dt><dd>{{ $sertifikat->jenis->nama_jenis }}</dd></div>
            <div><dt>Tanggal terbit</dt><dd>{{ $sertifikat->tanggal_terbit->locale('id')->translatedFormat('j F Y') }}</dd></div>
        </dl>
        <small>Informasi pribadi peserta tidak ditampilkan pada halaman verifikasi publik.</small>
    </main>
</body>
</html>
