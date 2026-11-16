@props(['jenisSertifikat', 'reissueCertificate' => null])

<section class="certificate-issuance-step certificate-issuance-details">
    <h2><span>1</span> Jenis sertifikat</h2>
    <div class="certificate-issuance-fields-grid">
        <label class="certificate-issuance-field">
            <span>Jenis sertifikat <b>*</b></span>
            <select name="id_jenis" required data-issuance-type>
                <option value="">Pilih jenis sertifikat</option>
                @foreach ($jenisSertifikat as $jenis)
                    @php($template = $jenis->template->first())
                    <option
                        value="{{ $jenis->id_jenis }}"
                        data-template-id="{{ $template->id_template }}"
                        data-template-name="{{ $template->nama_template }}"
                        data-template-url="{{ route('certificate-templates.preview', $template) }}"
                        data-type-name="{{ $jenis->nama_jenis }}"
                        data-type-code="{{ $jenis->kode }}"
                        @selected((string) old('id_jenis', $reissueCertificate?->id_jenis) === (string) $jenis->id_jenis)
                    >{{ $jenis->nama_jenis }}</option>
                @endforeach
            </select>
            @error('id_jenis') <small class="field-error">{{ $message }}</small> @enderror
        </label>
        <label class="certificate-issuance-field">
            <span>Tanggal terbit <b>*</b></span>
            <input type="date" name="tanggal_terbit" value="{{ old('tanggal_terbit', now()->toDateString()) }}" required data-issuance-date>
            @error('tanggal_terbit') <small class="field-error">{{ $message }}</small> @enderror
        </label>
    </div>
    <input type="hidden" name="id_template" value="{{ old('id_template') }}" data-issuance-template-id>
    <p class="certificate-issuance-template-used" data-issuance-template-used hidden></p>
</section>
