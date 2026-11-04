@props(['jenisSertifikat', 'reissueCertificate' => null])

<section class="certificate-issuance-step certificate-issuance-details">
    <h2><span>2</span> Jenis dan periode</h2>
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

    <div class="certificate-issuance-fields-grid certificate-issuance-period-fields">
        <label class="certificate-issuance-field">
            <span>Periode mulai <b>*</b></span>
            <input type="date" name="periode_mulai" value="{{ old('periode_mulai', $reissueCertificate?->periode_mulai?->toDateString()) }}" required data-issuance-start>
            @error('periode_mulai') <small class="field-error">{{ $message }}</small> @enderror
        </label>
        <label class="certificate-issuance-field">
            <span>Periode selesai <b>*</b></span>
            <input type="date" name="periode_selesai" value="{{ old('periode_selesai', $reissueCertificate?->periode_selesai?->toDateString()) }}" required data-issuance-end>
            @error('periode_selesai') <small class="field-error">{{ $message }}</small> @enderror
        </label>
    </div>
</section>

<section class="certificate-issuance-step certificate-issuance-support">
    <h2><span>3</span> Data pendukung <small>(opsional)</small></h2>
    <div class="certificate-issuance-fields-grid">
        <label class="certificate-issuance-field">
            <span>Durasi</span>
            <input type="text" name="durasi" maxlength="50" value="{{ old('durasi', $reissueCertificate?->durasi) }}" placeholder="Contoh: 3 bulan" data-issuance-duration>
            @error('durasi') <small class="field-error">{{ $message }}</small> @enderror
        </label>
        <label class="certificate-issuance-field">
            <span>Nilai</span>
            <input type="text" name="nilai" maxlength="20" value="{{ old('nilai', $reissueCertificate?->nilai) }}" placeholder="Contoh: 92" data-issuance-score>
            @error('nilai') <small class="field-error">{{ $message }}</small> @enderror
        </label>
        <label class="certificate-issuance-field">
            <span>Predikat</span>
            <input type="text" name="predikat" maxlength="30" value="{{ old('predikat', $reissueCertificate?->predikat) }}" placeholder="Contoh: Sangat Baik" data-issuance-predicate>
            @error('predikat') <small class="field-error">{{ $message }}</small> @enderror
        </label>
    </div>
</section>
