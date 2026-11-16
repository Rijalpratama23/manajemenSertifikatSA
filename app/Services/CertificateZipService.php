<?php

namespace App\Services;

use App\Models\Sertifikat;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class CertificateZipService
{
    public function download(Collection $certificates, string $archiveName): BinaryFileResponse
    {
        abort_unless(class_exists(ZipArchive::class), 501, 'Unduhan massal memerlukan ekstensi PHP Zip.');

        if ($certificates->isEmpty()) {
            throw ValidationException::withMessages([
                'download' => 'Tidak ada sertifikat yang sesuai untuk diunduh.',
            ]);
        }

        $disk = Storage::disk('local');
        foreach ($certificates as $certificate) {
            if (
                $certificate->pdf_status !== Sertifikat::PDF_STATUS_COMPLETED
                || ! $certificate->berkas_pdf
                || ! $disk->exists($certificate->berkas_pdf)
            ) {
                throw ValidationException::withMessages([
                    'download' => "PDF sertifikat {$certificate->nomor_sertifikat} belum tersedia. ZIP tidak dibuat agar unduhan tidak terpotong.",
                ]);
            }
        }

        $archivePath = tempnam(storage_path('app'), 'certificate-archive-');
        if ($archivePath === false) {
            throw new RuntimeException('File sementara untuk arsip ZIP tidak dapat dibuat.');
        }

        $archive = new ZipArchive;
        if ($archive->open($archivePath, ZipArchive::OVERWRITE) !== true) {
            unlink($archivePath);
            throw new RuntimeException('Arsip ZIP sertifikat tidak dapat dibuat.');
        }

        foreach ($certificates as $certificate) {
            $filename = preg_replace('/[^A-Za-z0-9._-]+/', '-', $certificate->nomor_sertifikat).'.pdf';
            if (! $archive->addFile($disk->path($certificate->berkas_pdf), $filename)) {
                $archive->close();
                unlink($archivePath);
                throw new RuntimeException("PDF sertifikat {$certificate->nomor_sertifikat} gagal ditambahkan ke arsip ZIP.");
            }
        }

        if (! $archive->close()) {
            unlink($archivePath);
            throw new RuntimeException('Arsip ZIP sertifikat gagal diselesaikan.');
        }

        return response()->download($archivePath, $archiveName)
            ->deleteFileAfterSend(true);
    }
}
