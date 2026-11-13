<?php

namespace App\Http\Controllers;

use App\Models\Sertifikat;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VerifikasiSertifikatController extends Controller
{
    public function show(string $token): View
    {
        $sertifikat = Sertifikat::query()
            ->with([
                'peserta:id_peserta,nama',
                'jenis:id_jenis,nama_jenis',
            ])
            ->where('token_publik', $token)
            ->firstOrFail();
        $hasPdf = $sertifikat->isAktif()
            && $sertifikat->berkas_pdf
            && Storage::disk('local')->exists($sertifikat->berkas_pdf);

        return view('certificates.verify', compact('sertifikat', 'hasPdf'));
    }

    public function preview(string $token): BinaryFileResponse
    {
        $sertifikat = $this->findActiveCertificate($token);
        $disk = Storage::disk('local');
        abort_unless($disk->exists($sertifikat->berkas_pdf), 404);

        return response()->file($disk->path($sertifikat->berkas_pdf), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$this->filename($sertifikat).'"',
        ]);
    }

    public function download(string $token): BinaryFileResponse
    {
        $sertifikat = $this->findActiveCertificate($token);
        $disk = Storage::disk('local');
        abort_unless($disk->exists($sertifikat->berkas_pdf), 404);

        return response()->download($disk->path($sertifikat->berkas_pdf), $this->filename($sertifikat));
    }

    private function findActiveCertificate(string $token): Sertifikat
    {
        $sertifikat = Sertifikat::query()
            ->where('token_publik', $token)
            ->firstOrFail();

        abort_unless($sertifikat->isAktif() && $sertifikat->berkas_pdf, 404);

        return $sertifikat;
    }

    private function filename(Sertifikat $sertifikat): string
    {
        return str_replace(['/', '\\'], '-', $sertifikat->nomor_sertifikat).'.pdf';
    }
}
