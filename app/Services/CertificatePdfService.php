<?php

namespace App\Services;

use App\Models\Sertifikat;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class CertificatePdfService
{
    public function render(Sertifikat $sertifikat): string
    {
        $disk = Storage::disk('local');
        $templatePath = $sertifikat->template->berkas_desain;

        if (! $disk->exists($templatePath)) {
            throw new RuntimeException('Berkas desain template sertifikat tidak ditemukan.');
        }

        $mimeType = $disk->mimeType($templatePath);
        if (! in_array($mimeType, ['image/jpeg', 'image/png'], true)) {
            throw new RuntimeException('Berkas desain template harus berupa PNG atau JPG.');
        }

        $background = sprintf(
            'data:%s;base64,%s',
            $mimeType,
            base64_encode($disk->get($templatePath)),
        );
        $verificationUrl = route('certificates.verify', $sertifikat->token_publik);
        $qrCode = (new Builder(
            writer: new PngWriter(),
            data: $verificationUrl,
            size: 150,
            margin: 0,
        ))->build()->getDataUri();

        return Pdf::loadView('certificates.pdf', compact('sertifikat', 'background', 'qrCode'))
            ->setPaper('a4', 'landscape')
            ->output();
    }
}
