@props(['peserta', 'reissueCertificate' => null])

<section class="certificate-issuance-step">
    <h2><span>1</span> Peserta</h2>
    <label class="certificate-issuance-field">
        <span>Peserta <b>*</b></span>
        <select name="id_peserta" required data-issuance-participant>
            <option value="">Pilih peserta</option>
            @foreach ($peserta as $item)
                <option
                    value="{{ $item->id_peserta }}"
                    data-email="{{ $item->email }}"
                    data-institution="{{ $item->asal_instansi ?? '' }}"
                    data-certificate-count="{{ $item->sertifikat_count }}"
                    @selected((string) old('id_peserta', $reissueCertificate?->id_peserta) === (string) $item->id_peserta)
                >{{ $item->nama }}</option>
            @endforeach
        </select>
        @error('id_peserta') <small class="field-error">{{ $message }}</small> @enderror
    </label>
    <div class="certificate-issuance-participant-info" data-issuance-participant-info hidden>
        <span data-participant-email></span>
        <span><span data-participant-institution></span><span data-participant-certificate-count></span></span>
    </div>
</section>
