<footer class="participant-table-footer">
    <p>
        @if ($participants->total() > 0)
            Menampilkan {{ $participants->firstItem() }}–{{ $participants->lastItem() }} dari {{ $participants->total() }} peserta
        @else
            Menampilkan 0 peserta
        @endif
    </p>
    {{ $participants->onEachSide(1)->links() }}
</footer>
