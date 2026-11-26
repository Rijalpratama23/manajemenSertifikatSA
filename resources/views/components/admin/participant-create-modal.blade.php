@props([
    'isCourseView' => false,
    'selectedType' => '',
    'selectedCertificateTypeName' => null,
])

<dialog
    class="participant-modal"
    aria-labelledby="participant-modal-title"
    data-participant-modal
    @if (! request()->filled('edit') && $errors->hasAny(['nama', 'nis', 'tempat_lahir', 'tanggal_lahir', 'email', 'asal_instansi'])) data-open-on-load @endif
>
    <div class="participant-modal-content">
        <header class="participant-modal-header">
            <h2 id="participant-modal-title">
                {{ $selectedCertificateTypeName ? 'Tambah Peserta '.$selectedCertificateTypeName : 'Tambah Peserta' }}
            </h2>
            <p>
                @if ($isCourseView)
                    Isi identitas peserta Kursus. NIS wajib diisi; data lahir dapat dilengkapi jika tersedia.
                @elseif ($selectedCertificateTypeName)
                    Isi data peserta {{ $selectedCertificateTypeName }}.
                @else
                    Isi data umum peserta baru untuk ditambahkan ke daftar.
                @endif
            </p>
        </header>

        <x-admin.participant-form
            :action="route('participants.store')"
            :cancel-url="route('participants.index')"
            submit-label="Simpan"
            :modal="true"
            :course-context="$isCourseView"
            :selected-type="$selectedType"
        />
    </div>
</dialog>
