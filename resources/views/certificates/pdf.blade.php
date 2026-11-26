<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    @php
        $courseCertificate = in_array($sertifikat->jenis->kode, ['KURSUS', '34'], true)
            || mb_strtolower(trim($sertifikat->jenis->nama_jenis)) === 'kursus';
        $support = (array) ($sertifikat->data_pendukung ?? []);
        $birthDetails = collect([
            $sertifikat->peserta->tempat_lahir,
            $sertifikat->peserta->tanggal_lahir?->locale('id')->translatedFormat('j F Y'),
        ])->filter()->implode(', ');
        $position = array_replace_recursive([
            'judul' => ['x' => 50, 'y' => 17, 'size' => 30],
            'diberikan' => ['x' => 50, 'y' => 31, 'size' => 13],
            'nama' => ['x' => 50, 'y' => 40, 'size' => 25],
            'nomor' => ['x' => 22, 'y' => 86, 'size' => 11],
            'deskripsi' => ['x' => 50, 'y' => 58, 'size' => 15],
            'tanggal' => ['x' => 50, 'y' => 76, 'size' => 13],
            'qr' => $courseCertificate
                ? ['x' => 25, 'y' => 77, 'size' => 9]
                : ['x' => 82, 'y' => 86, 'size' => 12],
        ], (array) ($sertifikat->template->konfigurasi_posisi ?? []));

        $pdfPosition = function (array $item, string $axis): string {
            $percentage = (float) ($item[$axis] ?? 50);
            $dimension = $axis === 'x' ? 297 : 210;
            return number_format($dimension * $percentage / 100, 2, '.', '').'mm';
        };
    @endphp
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
        .participant-identity { position: absolute; margin-top: -0.5em; font-size: 11pt; font-weight: bold; text-align: center; }
        .photo-placeholder { position: absolute; top: 77%; left: 75%; display: flex; width: 25.4mm; height: 33.9mm; align-items: center; justify-content: center; margin-left: -12.7mm; margin-top: -16.95mm; border: 0.5mm solid #243445; color: #243445; font-size: 11pt; font-weight: bold; }
    </style>
</head>
<body>
    <main class="certificate">
        <img class="background" src="{{ $background }}" alt="">
        <div class="content">
            @if ($position['judul']['visible'] ?? true)
                <div class="title" style="top: {{ $pdfPosition($position['judul'], 'y') }}; left: {{ $pdfPosition($position['judul'], 'x') }}; width: 90mm; margin-left:-45mm; margin-top:-0.5em; letter-spacing:normal; font-size: {{ $position['judul']['size'] }}pt;">SERTIFIKAT</div>
            @endif
            @if ($position['diberikan']['visible'] ?? true)
                <div class="given" style="top: {{ $pdfPosition($position['diberikan'], 'y') }}; left: {{ $pdfPosition($position['diberikan'], 'x') }}; width: 50mm; margin-left:-25mm; margin-top:-0.5em; font-size: {{ $position['diberikan']['size'] }}pt;">diberikan kepada</div>
            @endif
            @if ($position['nama']['visible'] ?? true)
                <div class="name" style="top: {{ $pdfPosition($position['nama'], 'y') }}; left: {{ $pdfPosition($position['nama'], 'x') }}; width: 160mm; margin-left:-80mm; margin-top:-0.5em; font-size: {{ $position['nama']['size'] }}pt;">{{ $sertifikat->peserta->nama }}</div>
            @endif
            @if ($courseCertificate)
                <div class="participant-identity" style="top: 47%; left: 50%; width: 180mm; margin-left: -90mm;">
                    @if ($birthDetails)
                        Tempat, Tgl. Lahir: {{ $birthDetails }}
                        &nbsp;&nbsp;
                    @endif
                    Nomor Induk: {{ $sertifikat->peserta->nis ?: '—' }}
                </div>
            @endif
            @if ($position['deskripsi']['visible'] ?? true)
                <div class="description" style="top: {{ $pdfPosition($position['deskripsi'], 'y') }}; left: {{ $pdfPosition($position['deskripsi'], 'x') }}; width: 180mm; margin-left:-90mm; margin-top:-0.5em; font-size: {{ $position['deskripsi']['size'] }}pt;">
                    {{ $sertifikat->deskripsi ?: 'telah menyelesaikan program '.$sertifikat->jenis->nama_jenis }}<br>
                    @if ($sertifikat->deskripsi && ! $courseCertificate)
                        {{ $sertifikat->jenis->nama_jenis }}<br>
                    @endif
                </div>
            @endif
            @if ($position['tanggal']['visible'] ?? true)
                <div class="details" style="top: {{ $pdfPosition($position['tanggal'], 'y') }}; left: {{ $pdfPosition($position['tanggal'], 'x') }}; width: 180mm; margin-left:-90mm; margin-top:-0.5em; font-size: {{ $position['tanggal']['size'] }}pt;">
                    @if ($courseCertificate)
                        {{ $support['kota_terbit'] ?? '' }}, {{ $sertifikat->tanggal_terbit->locale('id')->translatedFormat('d F Y') }}
                    @else
                        periode {{ $sertifikat->periode_mulai->locale('id')->translatedFormat('j M Y') }}
                        – {{ $sertifikat->periode_selesai->locale('id')->translatedFormat('j M Y') }}
                        @if ($sertifikat->durasi) ({{ $sertifikat->durasi }}) @endif
                    @endif
                </div>
            @endif
            @if (
                ! $courseCertificate
                &&
                ($sertifikat->nilai || $sertifikat->predikat || $sertifikat->data_pendukung)
                && ($position['deskripsi']['visible'] ?? true)
            )
                <div class="details" style="top: 82mm; left: 32mm; width:233mm;">
                    @if ($sertifikat->nilai) Nilai {{ $sertifikat->nilai }} @endif
                    @if ($sertifikat->nilai && $sertifikat->predikat) · @endif
                    @if ($sertifikat->predikat) {{ $sertifikat->predikat }} @endif
                    @foreach (config('certificate-issuance.types.'.$sertifikat->jenis->kode.'.fields', []) as $field)
                        @if (! isset($field['column']) && ! empty($sertifikat->data_pendukung[$field['key']] ?? null))
                            <div>{{ $field['label'] }}: {{ $sertifikat->data_pendukung[$field['key']] }}</div>
                        @endif
                    @endforeach
                </div>
            @endif
            @if ($position['nomor']['visible'] ?? true)
                <div class="number" style="left: {{ $pdfPosition($position['nomor'], 'x') }}; top: {{ $pdfPosition($position['nomor'], 'y') }}; bottom:auto; width:80mm; margin-left:-40mm; margin-top:-0.5em; text-align:center; font-size: {{ $position['nomor']['size'] }}pt;">Nomor: {{ $sertifikat->nomor_sertifikat }}</div>
            @endif
            @if ($position['qr']['visible'] ?? true)
                @php($qrSize = number_format(297 * (float) $position['qr']['size'] / 100, 2, '.', '').'mm')
                @php($qrOffset = number_format(297 * (float) $position['qr']['size'] / 200, 2, '.', '').'mm')
                <img class="verification" style="left: {{ $pdfPosition($position['qr'], 'x') }}; top: {{ $pdfPosition($position['qr'], 'y') }}; right:auto; bottom:auto; margin-left:-{{ $qrOffset }}; margin-top:-{{ $qrOffset }}; width: {{ $qrSize }}; height: {{ $qrSize }};" src="{{ $qrCode }}" alt="QR verifikasi">
            @endif
            @if ($courseCertificate)
                <div class="photo-placeholder" aria-label="Area foto 3 x 4">3 x 4</div>
            @endif
        </div>
    </main>
</body>
</html>
