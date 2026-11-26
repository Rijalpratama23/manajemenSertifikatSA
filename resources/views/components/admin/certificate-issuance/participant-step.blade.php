@props(['peserta', 'reissueCertificate' => null])

<section class="certificate-issuance-step">
    <h2><span>2</span> Peserta</h2>
    <p class="certificate-issuance-participant-hint" data-issuance-participant-hint>Pilih jenis sertifikat untuk melihat peserta yang dapat dipilih.</p>
    <div class="certificate-issuance-participant-search" data-issuance-participant-search-container hidden>
        <label class="visually-hidden" for="issuance-participant-search">Cari nama atau NIS peserta</label>
        <div class="certificate-issuance-participant-search-field">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="10.8" cy="10.8" r="6.3" stroke="currentColor" stroke-width="1.8"/>
                <path d="m15.5 15.5 4.2 4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
            <input
                id="issuance-participant-search"
                type="text"
                placeholder="Cari nama atau NIS peserta"
                autocomplete="off"
                role="combobox"
                aria-autocomplete="list"
                aria-haspopup="listbox"
                aria-controls="issuance-participant-suggestions"
                aria-expanded="false"
                data-issuance-participant-search
                disabled
            >
            <button type="button" aria-label="Hapus pencarian peserta" data-clear-issuance-participant-search hidden>
                <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="m5 5 10 10M15 5 5 15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
        <div
            class="certificate-type-search-suggestions certificate-issuance-participant-suggestions"
            id="issuance-participant-suggestions"
            role="listbox"
            data-issuance-participant-suggestions
            hidden
        ></div>
    </div>
    <fieldset class="certificate-issuance-participants" data-issuance-participants disabled>
        <legend class="visually-hidden">Pilih peserta sertifikat</legend>
        <label class="certificate-issuance-select-all" data-issuance-select-all-row hidden>
            <input type="checkbox" data-issuance-select-all>
            <span>Pilih semua peserta yang tampil</span>
        </label>
        @foreach ($peserta as $item)
            <label
                class="certificate-issuance-participant"
                data-issuance-participant-row
                data-course-participant="{{ $item->nis ? 'true' : 'false' }}"
                data-issued-types="{{ $item->sertifikat->where('status', \App\Models\Sertifikat::STATUS_AKTIF)->pluck('id_jenis')->implode(',') }}"
            >
                <input
                    type="checkbox"
                    name="id_peserta[]"
                    value="{{ $item->id_peserta }}"
                    data-participant-name="{{ $item->nama }}"
                    data-participant-birth-place="{{ $item->tempat_lahir ?? '' }}"
                    data-participant-birth-date="{{ $item->tanggal_lahir ? $item->tanggal_lahir->locale('id')->translatedFormat('j F Y') : '' }}"
                    data-participant-nis="{{ $item->nis ?? '' }}"
                    @checked(in_array((string) $item->id_peserta, array_map('strval', (array) old('id_peserta', $reissueCertificate ? [$reissueCertificate->id_peserta] : [])), true))
                >
                <span>
                    <strong>{{ $item->nama }}</strong>
                    <small>
                        NIS {{ $item->nis ?: '—' }} ·
                        {{ $item->tempat_lahir || $item->tanggal_lahir ? trim($item->tempat_lahir.' '.($item->tanggal_lahir?->locale('id')->translatedFormat('j F Y') ?? '')) : 'Data lahir belum diisi (opsional)' }}
                    </small>
                </span>
                <small class="certificate-issuance-participant-count">{{ $item->sertifikat_count }} sertifikat</small>
            </label>
        @endforeach
        <p class="certificate-issuance-participants-empty" data-issuance-participants-empty hidden>Tidak ada peserta yang memenuhi pilihan untuk jenis sertifikat ini.</p>
        <p class="certificate-issuance-search-empty" data-issuance-search-empty hidden>Tidak ditemukan peserta yang cocok. Coba nama atau NIS lain.</p>
    </fieldset>
    @error('id_peserta') <small class="field-error">{{ $message }}</small> @enderror
    @error('id_peserta.*') <small class="field-error">{{ $message }}</small> @enderror
</section>
