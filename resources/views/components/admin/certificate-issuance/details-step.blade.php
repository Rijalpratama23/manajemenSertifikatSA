@props(['reissueCertificate' => null, 'issuanceFieldsByType' => []])

<section class="certificate-issuance-step certificate-issuance-details">
    <h2><span>3</span> Periode</h2>
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
    <h2><span>4</span> Data pendukung</h2>
    @foreach ($issuanceFieldsByType as $typeCode => $schema)
        <fieldset
            class="certificate-issuance-type-fields"
            data-issuance-type-fields="{{ $typeCode }}"
            data-course-certificate="{{ ($schema['course_certificate'] ?? false) ? 'true' : 'false' }}"
            disabled
            hidden
        >
            <legend class="visually-hidden">{{ $schema['title'] }}</legend>
            <h3>{{ $schema['title'] }}</h3>
            <div class="certificate-issuance-fields-grid">
                @foreach ($schema['fields'] as $field)
                    @php
                        $reissueValue = isset($field['column'])
                            ? $reissueCertificate?->{$field['column']}
                            : ($reissueCertificate?->data_pendukung[$field['key']] ?? '');
                        $fieldValue = old('data_pendukung.'.$field['key'], $reissueValue);
                        $fieldMax = $field['max'] ?? 150;
                    @endphp
                    <label class="certificate-issuance-field">
                        <span>{{ $field['label'] }} @if ($field['required']) <b>*</b> @endif</span>
                        <input
                            type="text"
                            name="data_pendukung[{{ $field['key'] }}]"
                            maxlength="{{ $fieldMax }}"
                            value="{{ $fieldValue }}"
                            placeholder="{{ $field['placeholder'] }}"
                            @required($field['required'])
                            data-support-input
                            data-support-key="{{ $field['key'] }}"
                            data-support-label="{{ $field['label'] }}"
                            data-support-column="{{ $field['column'] ?? '' }}"
                        >
                        @if ($errors->has('data_pendukung.'.$field['key']))
                            <small class="field-error">{{ $errors->first('data_pendukung.'.$field['key']) }}</small>
                        @endif
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endforeach
    @error('data_pendukung') <small class="field-error">{{ $message }}</small> @enderror
    <label class="certificate-issuance-field certificate-issuance-description-field">
        <span data-issuance-description-label>Deskripsi sertifikat <b>*</b></span>
        <textarea name="deskripsi" maxlength="500" required placeholder="Contoh: Telah menyelesaikan program dengan baik" data-issuance-description>{{ old('deskripsi', $reissueCertificate?->deskripsi) }}</textarea>
        @error('deskripsi') <small class="field-error">{{ $message }}</small> @enderror
    </label>
</section>
