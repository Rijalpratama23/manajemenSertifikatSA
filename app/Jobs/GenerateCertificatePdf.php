<?php

namespace App\Jobs;

use App\Models\Sertifikat;
use App\Services\CertificatePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class GenerateCertificatePdf implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 240;

    public function __construct(public int $certificateId) {}

    public function handle(CertificatePdfService $pdfService): void
    {
        $certificate = Sertifikat::query()
            ->with(['peserta', 'jenis', 'template'])
            ->findOrFail($this->certificateId);
        $disk = Storage::disk('local');

        if (
            $certificate->pdf_status === Sertifikat::PDF_STATUS_COMPLETED
            && $certificate->berkas_pdf
            && $disk->exists($certificate->berkas_pdf)
        ) {
            return;
        }

        $certificate->update(['pdf_status' => Sertifikat::PDF_STATUS_PROCESSING]);
        $pdfPath = sprintf('certificates/%d-%s.pdf', $certificate->getKey(), bin2hex(random_bytes(8)));

        try {
            if (! $disk->put($pdfPath, $pdfService->render($certificate))) {
                throw new RuntimeException('PDF sertifikat gagal disimpan.');
            }

            $certificate->update([
                'berkas_pdf' => $pdfPath,
                'pdf_status' => Sertifikat::PDF_STATUS_COMPLETED,
            ]);
        } catch (Throwable $exception) {
            if ($disk->exists($pdfPath) && ! $disk->delete($pdfPath)) {
                logger()->warning('Gagal membersihkan PDF sementara setelah proses pembuatan PDF gagal.', [
                    'path' => $pdfPath,
                    'certificate_id' => $certificate->getKey(),
                ]);
            }

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        Sertifikat::query()
            ->whereKey($this->certificateId)
            ->update(['pdf_status' => Sertifikat::PDF_STATUS_FAILED]);

        logger()->error('Pembuatan PDF sertifikat gagal setelah seluruh percobaan.', [
            'certificate_id' => $this->certificateId,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
