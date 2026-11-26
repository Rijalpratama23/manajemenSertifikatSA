<footer class="participant-table-footer">
    <p>
        @if ($jenisSertifikat->total() > 0)
            Menampilkan {{ $jenisSertifikat->firstItem() }}–{{ $jenisSertifikat->lastItem() }} dari {{ $jenisSertifikat->total() }} jenis sertifikat
        @else
            Menampilkan 0 jenis sertifikat
        @endif
    </p>
    {{ $jenisSertifikat->onEachSide(1)->links() }}
</footer>
