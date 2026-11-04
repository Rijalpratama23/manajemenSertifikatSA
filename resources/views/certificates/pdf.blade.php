<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; size: A4 landscape; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #243445; font-family: DejaVu Sans, sans-serif; }
        .certificate { position: relative; width: 297mm; height: 210mm; overflow: hidden; }
        .background { position: absolute; inset: 0; width: 297mm; height: 210mm; }
        .content { position: absolute; inset: 0; text-align: center; }
        .title { position: absolute; top: 26mm; left: 20mm; width: 257mm; font-size: 30pt; letter-spacing: 5pt; font-weight: bold; }
        .given { position: absolute; top: 59mm; left: 20mm; width: 257mm; font-size: 13pt; }
        .name { position: absolute; top: 72mm; left: 20mm; width: 257mm; font-size: 25pt; font-weight: bold; }
        .description { position: absolute; top: 105mm; left: 32mm; width: 233mm; font-size: 15pt; line-height: 1.5; }
        .details { position: absolute; top: 145mm; left: 32mm; width: 233mm; font-size: 13pt; }
        .number { position: absolute; left: 22mm; bottom: 18mm; text-align: left; font-size: 10pt; }
        .verification { position: absolute; right: 20mm; bottom: 16mm; width: 31mm; height: 31mm; }
    </style>
</head>
<body>
    <main class="certificate">
        <img class="background" src="{{ $background }}" alt="">
        <div class="content">
            <div class="title">SERTIFIKAT</div>
            <div class="given">diberikan kepada</div>
            <div class="name">{{ $sertifikat->peserta->nama }}</div>
            <div class="description">
                telah menyelesaikan program {{ $sertifikat->jenis->nama_jenis }}<br>
                periode {{ $sertifikat->periode_mulai->locale('id')->translatedFormat('j M Y') }}
                – {{ $sertifikat->periode_selesai->locale('id')->translatedFormat('j M Y') }}
                @if ($sertifikat->durasi) ({{ $sertifikat->durasi }}) @endif
            </div>
            @if ($sertifikat->nilai || $sertifikat->predikat)
                <div class="details">
                    @if ($sertifikat->nilai) Nilai {{ $sertifikat->nilai }} @endif
                    @if ($sertifikat->nilai && $sertifikat->predikat) · @endif
                    @if ($sertifikat->predikat) {{ $sertifikat->predikat }} @endif
                </div>
            @endif
            <div class="number">Nomor: {{ $sertifikat->nomor_sertifikat }}</div>
            <img class="verification" src="{{ $qrCode }}" alt="QR verifikasi">
        </div>
    </main>
</body>
</html>
