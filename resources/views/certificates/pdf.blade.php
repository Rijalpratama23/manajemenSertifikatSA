<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    @php
        $position = array_replace_recursive([
            'judul' => ['x' => 50, 'y' => 17, 'size' => 30],
            'diberikan' => ['x' => 50, 'y' => 31, 'size' => 13],
            'nama' => ['x' => 50, 'y' => 40, 'size' => 25],
            'nomor' => ['x' => 22, 'y' => 86, 'size' => 11],
            'deskripsi' => ['x' => 50, 'y' => 58, 'size' => 15],
            'tanggal' => ['x' => 50, 'y' => 76, 'size' => 13],
            'qr' => ['x' => 82, 'y' => 86, 'size' => 12],
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
    </style>
</head>
<body>
    <main class="certificate">
        <img class="background" src="{{ $background }}" alt="">
        <div class="content">
            <div class="title" style="top: {{ $pdfPosition($position['judul'], 'y') }}; left: {{ $pdfPosition($position['judul'], 'x') }}; width: 50mm; margin-left:-25mm; font-size: {{ $position['judul']['size'] }}pt;">SERTIFIKAT</div>
            <div class="given" style="top: {{ $pdfPosition($position['diberikan'], 'y') }}; left: {{ $pdfPosition($position['diberikan'], 'x') }}; width: 50mm; margin-left:-25mm; font-size: {{ $position['diberikan']['size'] }}pt;">diberikan kepada</div>
            <div class="name" style="top: {{ $pdfPosition($position['nama'], 'y') }}; left: {{ $pdfPosition($position['nama'], 'x') }}; width: 160mm; margin-left:-80mm; font-size: {{ $position['nama']['size'] }}pt;">{{ $sertifikat->peserta->nama }}</div>
            <div class="description" style="top: {{ $pdfPosition($position['deskripsi'], 'y') }}; left: {{ $pdfPosition($position['deskripsi'], 'x') }}; width: 180mm; margin-left:-90mm; font-size: {{ $position['deskripsi']['size'] }}pt;">
                {{ $sertifikat->deskripsi ?: 'telah menyelesaikan program '.$sertifikat->jenis->nama_jenis }}<br>
                @if ($sertifikat->deskripsi)
                    {{ $sertifikat->jenis->nama_jenis }}<br>
                @endif
            </div>
            <div class="details" style="top: {{ $pdfPosition($position['tanggal'], 'y') }}; left: {{ $pdfPosition($position['tanggal'], 'x') }}; width: 180mm; margin-left:-90mm; font-size: {{ $position['tanggal']['size'] }}pt;">
                periode {{ $sertifikat->periode_mulai->locale('id')->translatedFormat('j M Y') }}
                – {{ $sertifikat->periode_selesai->locale('id')->translatedFormat('j M Y') }}
                @if ($sertifikat->durasi) ({{ $sertifikat->durasi }}) @endif
            </div>
            @if ($sertifikat->nilai || $sertifikat->predikat || $sertifikat->data_pendukung)
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
            <div class="number" style="left: {{ $pdfPosition($position['nomor'], 'x') }}; top: {{ $pdfPosition($position['nomor'], 'y') }}; bottom:auto; font-size: {{ $position['nomor']['size'] }}pt;">Nomor: {{ $sertifikat->nomor_sertifikat }}</div>
            @php($qrSize = number_format(297 * (float) $position['qr']['size'] / 100, 2, '.', '').'mm')
            <img class="verification" style="left: {{ $pdfPosition($position['qr'], 'x') }}; top: {{ $pdfPosition($position['qr'], 'y') }}; right:auto; bottom:auto; width: {{ $qrSize }}; height: {{ $qrSize }};" src="{{ $qrCode }}" alt="QR verifikasi">
        </div>
    </main>
</body>
</html>
