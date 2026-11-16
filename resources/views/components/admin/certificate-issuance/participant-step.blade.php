@props(['peserta', 'reissueCertificate' => null])

<section class="certificate-issuance-step">
    <h2><span>2</span> Peserta</h2>
    <p class="certificate-issuance-participant-hint" data-issuance-participant-hint>Pilih jenis sertifikat untuk melihat peserta yang dapat dipilih.</p>
    <fieldset class="certificate-issuance-participants" data-issuance-participants disabled>
        <legend class="visually-hidden">Pilih peserta sertifikat</legend>
        <label class="certificate-issuance-select-all" data-issuance-select-all-row hidden>
            <input type="checkbox" data-issuance-select-all>
            <span>Pilih semua peserta yang tampil</span>
        </label>
        @foreach ($peserta as $item)
            <label class="certificate-issuance-participant" data-issuance-participant-row data-issued-types="{{ $item->sertifikat->where('status', \App\Models\Sertifikat::STATUS_AKTIF)->pluck('id_jenis')->implode(',') }}">
                <input
                    type="checkbox"
                    name="id_peserta[]"
                    value="{{ $item->id_peserta }}"
                    data-participant-name="{{ $item->nama }}"
                    @checked(in_array((string) $item->id_peserta, array_map('strval', (array) old('id_peserta', $reissueCertificate ? [$reissueCertificate->id_peserta] : [])), true))
                >
                <span>
                    <strong>{{ $item->nama }}</strong>
                    <small>{{ $item->email }} · {{ $item->asal_instansi ?: 'Asal instansi belum diisi' }}</small>
                </span>
                <small class="certificate-issuance-participant-count">{{ $item->sertifikat_count }} sertifikat</small>
            </label>
        @endforeach
        <p class="certificate-issuance-participants-empty" data-issuance-participants-empty hidden>Tidak ada peserta yang memenuhi pilihan untuk jenis sertifikat ini.</p>
    </fieldset>
    @error('id_peserta') <small class="field-error">{{ $message }}</small> @enderror
    @error('id_peserta.*') <small class="field-error">{{ $message }}</small> @enderror
</section>
