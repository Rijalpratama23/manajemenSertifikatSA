@props(['template'])

@php
    $fileUrl = route('certificate-templates.preview', $template);
    $isPdf = \Illuminate\Support\Str::endsWith(strtolower($template->berkas_desain), '.pdf');
@endphp

<article
    class="certificate-template-card"
    data-template-card
    data-template-id="{{ $template->id_template }}"
    data-template-type-id="{{ $template->id_jenis }}"
    data-template-name="{{ $template->nama_template }}"
    data-template-active="{{ $template->aktif ? 'true' : 'false' }}"
>
    <button
        class="certificate-template-preview-trigger"
        type="button"
        aria-label="Pratinjau {{ $template->nama_template }}"
        data-open-template-preview
        data-preview-url="{{ $fileUrl }}"
        data-preview-type="{{ $isPdf ? 'pdf' : 'image' }}"
        data-preview-name="{{ $template->nama_template }}"
    >
        @if ($isPdf)
            <span class="certificate-template-file-placeholder">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M6 3.5h8l4.5 4.6v12.4H6V3.5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                    <path d="M14 3.8v4.7h4.2M9 13h6m-6 3h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                </svg>
                <strong>PDF</strong>
            </span>
        @else
            <img src="{{ $fileUrl }}" alt="Desain {{ $template->nama_template }}" loading="lazy">
        @endif
        <span class="certificate-template-preview-label">Pratinjau</span>
    </button>

    <div class="certificate-template-card-meta">
        <span class="certificate-template-type">{{ $template->jenis->nama_jenis }}</span>
        <span class="certificate-type-status {{ $template->aktif ? 'is-active' : 'is-inactive' }}">
            {{ $template->aktif ? 'Aktif' : 'Nonaktif' }}
        </span>
    </div>
    <h2 class="certificate-template-name">{{ $template->nama_template }}</h2>

    <div class="certificate-template-actions">
        <button
            class="certificate-template-action"
            type="button"
            data-open-template-preview
            data-preview-url="{{ $fileUrl }}"
            data-preview-type="{{ $isPdf ? 'pdf' : 'image' }}"
            data-preview-name="{{ $template->nama_template }}"
        >Pratinjau</button>
        <a class="certificate-template-action" href="{{ route('certificate-templates.edit', $template) }}">Ubah</a>
        <form
            method="POST"
            action="{{ route('certificate-templates.toggle', $template) }}"
            @if ($template->aktif)
                data-confirm
                data-confirm-style="template"
                data-confirm-step="1. Nonaktifkan"
                data-confirm-icon="pause"
                data-confirm-title='Nonaktifkan "{{ $template->nama_template }}"?'
                data-confirm-message="Jenis {{ $template->jenis->nama_jenis }} tidak dapat menerbitkan sertifikat baru sampai memiliki template aktif. Sertifikat yang sudah terbit tidak berubah."
                data-confirm-label="Nonaktifkan"
                data-confirm-tone="warning"
            @endif
            data-template-toggle
            data-template-type-id="{{ $template->id_jenis }}"
            data-template-id="{{ $template->id_template }}"
            data-template-name="{{ $template->nama_template }}"
            data-template-type-name="{{ $template->jenis->nama_jenis }}"
            data-template-active="{{ $template->aktif ? 'true' : 'false' }}"
        >
            @csrf
            @method('PATCH')
            <button class="certificate-template-action {{ $template->aktif ? 'is-warning' : 'is-success' }}" type="submit">
                {{ $template->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
            </button>
        </form>
        <form
            method="POST"
            action="{{ route('certificate-templates.destroy', $template) }}"
            data-confirm
            data-confirm-style="template"
            data-confirm-step="2. Hapus"
            data-confirm-icon="delete"
            data-confirm-title='Hapus "{{ $template->nama_template }}"?'
            data-confirm-message="Template ini belum pernah dipakai. Data dan berkas desain akan dihapus dan tidak dapat dikembalikan."
            data-confirm-label="Hapus"
            data-confirm-tone="danger"
        >
            @csrf
            @method('DELETE')
            <button
                class="certificate-template-action is-danger"
                type="submit"
                @disabled($template->sertifikat_count > 0)
                title="{{ $template->sertifikat_count > 0 ? 'Template sudah digunakan oleh sertifikat' : 'Hapus template' }}"
            >Hapus</button>
        </form>
    </div>
    @if ($template->sertifikat_count > 0)
        <p class="certificate-template-protected">{{ $template->sertifikat_count }} sertifikat menggunakan template ini</p>
    @endif
</article>
