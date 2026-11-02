@props(['jenis'])

<div class="certificate-type-actions">
    <button
        class="certificate-type-action certificate-type-action-edit"
        type="button"
        data-open-certificate-type-edit
        data-edit-url="{{ route('certificate-types.update', $jenis) }}"
        data-type-code="{{ $jenis->kode }}"
        data-type-name="{{ $jenis->nama_jenis }}"
        data-type-active="{{ $jenis->aktif ? '1' : '0' }}"
        data-code-locked="{{ $jenis->sertifikat_count > 0 ? 'true' : 'false' }}"
    >Ubah</button>

    <form
        method="POST"
        action="{{ route('certificate-types.toggle', $jenis) }}"
        @if ($jenis->aktif)
            data-confirm="Nonaktifkan jenis sertifikat {{ $jenis->nama_jenis }}?"
            data-confirm-title="Yakin Ingin Menonaktifkan?"
            data-confirm-message="Jenis sertifikat ini akan dinonaktifkan."
        @endif
    >
        @csrf
        @method('PATCH')
        <button class="certificate-type-action {{ $jenis->aktif ? 'certificate-type-action-disable' : 'certificate-type-action-enable' }}" type="submit">
            {{ $jenis->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
        </button>
    </form>

    <form
        method="POST"
        action="{{ route('certificate-types.destroy', $jenis) }}"
        data-confirm="Hapus jenis sertifikat {{ $jenis->nama_jenis }}?"
        data-confirm-title="Yakin Ingin Menghapus?"
        data-confirm-message="Data akan dihapus secara permanen."
    >
        @csrf
        @method('DELETE')
        <button class="certificate-type-action certificate-type-action-delete" type="submit">Hapus</button>
    </form>
</div>
