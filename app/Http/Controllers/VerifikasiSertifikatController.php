<?php

namespace App\Http\Controllers;

use App\Models\Sertifikat;
use Illuminate\Contracts\View\View;

class VerifikasiSertifikatController extends Controller
{
    public function show(string $token): View
    {
        $sertifikat = Sertifikat::query()
            ->with(['jenis:id_jenis,nama_jenis'])
            ->where('token_publik', $token)
            ->firstOrFail();

        return view('certificates.verify', compact('sertifikat'));
    }
}
