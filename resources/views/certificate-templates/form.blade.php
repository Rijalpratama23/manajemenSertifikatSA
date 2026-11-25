<x-admin.layout :title="$editTemplate ? 'Ubah Template Sertifikat' : 'Tambah Template Sertifikat'">
    @php($existingDesignIsImage = $editTemplate && \Illuminate\Support\Str::endsWith(strtolower($editTemplate->berkas_desain), ['.jpg', '.jpeg', '.png']))
    @php($typeLocked = $editTemplate && ($editTemplate->sertifikat()->exists() || ! $editTemplate->jenis()->where('aktif', true)->exists()))
    <div class="certificate-templates-content certificate-template-editor">
        <x-admin.flash-message />

        <a class="certificate-template-back" href="{{ route('certificate-templates.index') }}" aria-label="Kembali ke daftar template">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="m15 18-6-6 6-6M9 12h11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
        <header class="certificate-template-editor-heading">
            <h1>{{ $editTemplate ? 'Ubah Template Sertifikat' : 'Tambah Template Sertifikat' }}</h1>
            <p>Isi data dasar, sesuaikan posisi, dan pilih elemen yang ingin ditampilkan pada template.</p>
        </header>

        <form
            class="certificate-template-editor-form"
            method="POST"
            action="{{ $editTemplate ? route('certificate-templates.update', $editTemplate) : route('certificate-templates.store') }}"
            enctype="multipart/form-data"
            data-certificate-template-form
            data-template-id="{{ $editTemplate?->id_template ?? '' }}"
            data-template-active-initial="{{ $editTemplate?->aktif ? 'true' : 'false' }}"
        >
            @csrf
            @if ($editTemplate)
                @method('PUT')
            @endif
            <div class="certificate-template-editor-panel">
                <div class="certificate-template-editor-fields">
                    <label class="certificate-template-editor-field">
                        <span>Nama Template <b>*</b></span>
                        <span class="certificate-template-editor-input">
                            <input
                                type="text"
                                name="nama_template"
                                maxlength="100"
                                value="{{ old('nama_template', $editTemplate?->nama_template) }}"
                                placeholder="Contoh: Template Magang"
                                required
                                data-template-name
                            >
                            <small><span data-template-name-count>{{ mb_strlen(old('nama_template', $editTemplate?->nama_template ?? '')) }}</span>/100</small>
                        </span>
                        @error('nama_template') <small class="field-error">{{ $message }}</small> @enderror
                    </label>

                    <label class="certificate-template-editor-field">
                        <span>Jenis Sertifikat <b>*</b></span>
                        <select
                            name="id_jenis"
                            required
                            data-template-type
                            @disabled($typeLocked)
                        >
                            <option value="">Pilih jenis sertifikat</option>
                            @foreach ($jenisSertifikat as $jenis)
                                <option
                                    value="{{ $jenis->id_jenis }}"
                                    data-course-template="{{ in_array($jenis->kode, ['KURSUS', '34'], true) || mb_strtolower(trim($jenis->nama_jenis)) === 'kursus' ? 'true' : 'false' }}"
                                    @selected((string) old('id_jenis', $selectedType) === (string) $jenis->id_jenis)
                                >{{ $jenis->nama_jenis }}</option>
                            @endforeach
                        </select>
                        @if ($typeLocked)
                            <small class="certificate-template-editor-help">Jenis tidak dapat diubah karena template sudah digunakan oleh sertifikat.</small>
                            <input type="hidden" name="id_jenis" value="{{ $editTemplate->id_jenis }}">
                        @else
                            <small class="certificate-template-editor-help">Hanya jenis yang aktif yang tampil</small>
                        @endif
                        @error('id_jenis') <small class="field-error">{{ $message }}</small> @enderror
                    </label>

                    <label class="certificate-template-editor-field">
                        <span>Status</span>
                        <select name="aktif" data-template-active>
                            <option value="1" @selected((string) old('aktif', $editTemplate ? (int) $editTemplate->aktif : 1) === '1')>Aktif</option>
                            <option value="0" @selected((string) old('aktif', $editTemplate ? (int) $editTemplate->aktif : 1) === '0')>Nonaktif</option>
                        </select>
                        @error('aktif') <small class="field-error">{{ $message }}</small> @enderror
                    </label>

                    <label class="certificate-template-editor-field">
                        <span>Desain Latar @if (!$editTemplate)<b>*</b>@endif</span>
                        <span class="certificate-template-upload-control">
                            <input
                                type="file"
                                name="berkas_desain"
                                accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                @required(!$editTemplate)
                                data-template-file-input
                            >
                            <span data-template-file-name>
                                {{ $editTemplate ? basename($editTemplate->berkas_desain) : 'Unggah desain latar' }}
                            </span>
                        </span>
                        <small class="certificate-template-editor-help" data-template-file-help>
                            PNG atau JPG, maksimal 5 MB, lanskap A4 (minimal 1403 × 992px, sekitar 120 DPI).
                            @if ($editTemplate)
                                Desain saat ini tetap digunakan jika tidak ada berkas baru yang dipilih.
                            @endif
                        </small>
                        <div
                            class="admin-alert admin-alert-error certificate-template-upload-error"
                            role="alert"
                            aria-live="assertive"
                            data-template-client-error
                            hidden
                        ></div>
                        @error('berkas_desain') <small class="field-error">{{ $message }}</small> @enderror
                    </label>

                    <section class="certificate-template-position-editor" aria-labelledby="template-position-heading">
                        <header>
                            <div>
                                <h2 id="template-position-heading"><span aria-hidden="true"></span>Atur Elemen Sertifikat</h2>
                                <p>Atur posisi dan ukuran, atau nonaktifkan elemen yang tidak diperlukan pada desain ini.</p>
                                <p class="certificate-template-position-note">Pengaturan ini menyembunyikan elemen yang ditambahkan sistem. Teks yang sudah menyatu di gambar desain perlu dihapus dari file gambar sebelum diunggah.</p>
                            </div>
                            <button type="button" class="certificate-template-position-reset" data-reset-template-position>
                                Reset Posisi
                            </button>
                        </header>

                        <div class="certificate-template-position-grid">
                            @foreach ($positionElements as $key => $element)
                                @php($position = $positionConfig[$key])
                                @php($positionVisible = old('posisi.'.$key.'.visible', $position['visible'] ?? true))
                                <fieldset class="certificate-template-position-card is-{{ $element['color'] }}" data-position-card="{{ $key }}">
                                    <legend>{{ $element['label'] }}</legend>
                                    <label class="certificate-template-position-visibility">
                                        <input type="hidden" name="posisi[{{ $key }}][visible]" value="0">
                                        <input
                                            type="checkbox"
                                            name="posisi[{{ $key }}][visible]"
                                            value="1"
                                            data-position-visible="{{ $key }}"
                                            @if ($positionVisible) checked @endif
                                        >
                                        <span>Tampilkan elemen ini</span>
                                    </label>
                                    <label>
                                        <span>X (%)</span>
                                        <input type="text" inputmode="decimal" name="posisi[{{ $key }}][x]" value="{{ old('posisi.'.$key.'.x', $position['x']) }}" data-position-x="{{ $key }}">
                                        @error('posisi.'.$key.'.x') <small class="field-error">{{ $message }}</small> @enderror
                                    </label>
                                    <label>
                                        <span>Y (%)</span>
                                        <input type="text" inputmode="decimal" name="posisi[{{ $key }}][y]" value="{{ old('posisi.'.$key.'.y', $position['y']) }}" data-position-y="{{ $key }}">
                                        @error('posisi.'.$key.'.y') <small class="field-error">{{ $message }}</small> @enderror
                                    </label>
                                    <label>
                                        <span>Ukuran</span>
                                        <input type="number" name="posisi[{{ $key }}][size]" value="{{ old('posisi.'.$key.'.size', $position['size']) }}" min="{{ $key === 'qr' ? 5 : 6 }}" max="{{ in_array($key, ['nama', 'judul']) ? 60 : 40 }}" step="1" data-position-size="{{ $key }}">
                                    </label>
                                </fieldset>
                            @endforeach
                        </div>

                        <div class="certificate-template-live-preview-canvas" data-template-position-canvas>
                            <img src="{{ $existingDesignIsImage ? route('certificate-templates.preview', $editTemplate) : '' }}" alt="" data-template-position-background @if (!$existingDesignIsImage) hidden @endif>
                            @foreach ($positionElements as $key => $element)
                                <span
                                    class="certificate-template-position-item is-{{ $key }}"
                                    data-position-item="{{ $key }}"
                                    data-position-key="{{ $key }}"
                                    @if (! $positionVisible) hidden @endif
                                >
                                    @switch($key)
                                        @case('judul') SERTIFIKAT @break
                                        @case('diberikan') diberikan kepada @break
                                        @case('nama') Nama Peserta @break
                                        @case('nomor') Nomor: SA/2026/0001 @break
                                        @case('deskripsi') Telah menyelesaikan program pelatihan dengan baik @break
                                        @case('tanggal') Periode 1 Nov 2026 – 23 Nov 2026 @break
                                        @case('qr') QR @break
                                    @endswitch
                                </span>
                            @endforeach
                        </div>
                    </section>

                    <section class="certificate-template-live-preview" aria-labelledby="template-preview-heading">
                        <h2 id="template-preview-heading">Pratinjau</h2>
                        <div class="certificate-template-live-preview-card" data-template-preview-card>
                            <img
                                src="{{ $existingDesignIsImage ? route('certificate-templates.preview', $editTemplate) : '' }}"
                                alt="Pratinjau desain latar"
                                data-template-preview-image
                                @if (!$existingDesignIsImage) hidden @endif
                            >
                            <div class="certificate-template-preview-placeholder" data-template-preview-placeholder @if ($existingDesignIsImage) hidden @endif>
                                <p>Pratinjau tampil setelah desain latar diunggah</p>
                            </div>
                            <button
                                class="certificate-template-live-preview-button"
                                type="button"
                                data-open-template-form-preview
                                @disabled(!$existingDesignIsImage)
                            >Lihat Pratinjau</button>
                        </div>
                        <small>Pratinjau memakai data contoh dan tidak tercetak</small>
                    </section>
                </div>
            </div>

            <div class="certificate-template-editor-actions">
                <a class="certificate-template-editor-cancel" href="{{ route('certificate-templates.index') }}">Batal</a>
                <button class="participant-primary-button" type="submit">
                    {{ $editTemplate ? 'Simpan Perubahan' : 'Simpan Template' }}
                </button>
            </div>
        </form>

        <div hidden data-active-template-records>
            @foreach ($activeTemplates as $activeTemplate)
                <span
                    data-active-template
                    data-template-id="{{ $activeTemplate->id_template }}"
                    data-template-type-id="{{ $activeTemplate->id_jenis }}"
                    data-template-name="{{ $activeTemplate->nama_template }}"
                    data-template-type-name="{{ $activeTemplate->jenis->nama_jenis }}"
                ></span>
            @endforeach
        </div>

        <dialog class="certificate-template-form-preview" aria-labelledby="template-form-preview-title" data-template-form-preview>
            <button class="certificate-template-preview-close" type="button" aria-label="Tutup pratinjau" data-close-template-form-preview>&times;</button>
            <h2 id="template-form-preview-title">Pratinjau Sertifikat</h2>
            <div class="certificate-template-form-preview-canvas" data-template-preview-canvas>
                <span class="preview-sample-title">SERTIFIKAT</span>
                <span class="preview-sample-given">diberikan kepada</span>
                <span class="preview-sample-recipient">Nama Peserta</span>
                <span class="preview-sample-description">Telah menyelesaikan program pelatihan dengan baik</span>
                <span class="preview-sample-date">Periode 1 Nov 2026 – 23 Nov 2026</span>
                <span class="preview-sample-qr">QR</span>
                <span class="preview-sample-number">Nomor: SA/2026/0001</span>
            </div>
        </dialog>
    </div>
</x-admin.layout>
