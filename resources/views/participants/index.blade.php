<x-admin.layout title="Data Peserta">
    <div class="participants-content">
        <x-admin.flash-message />

        <header class="dashboard-header participants-header">
            <div>
                <h1>Data Peserta</h1>
                <p>Pengolahan Data Peserta</p>
            </div>
            <div class="participants-header-actions">
                <form class="participant-search" method="GET" action="{{ route('participants.index') }}" role="search">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="10.8" cy="10.8" r="6.3" stroke="currentColor" stroke-width="1.8"/>
                        <path d="m15.5 15.5 4.2 4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari peserta"
                        aria-label="Cari nama, email, atau instansi peserta"
                    >
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                </form>
                <button class="participant-primary-button" type="button" data-open-participant-modal>Tambah Peserta</button>
                <x-admin.participant-import />
            </div>
        </header>

        <x-admin.panel title="Tabel Peserta" :subtitle="'Menampilkan ' . $participants->count() . ' dari ' . $participants->total() . ' peserta'" class="participants-panel">
            <div class="participant-table-toolbar">
                <form method="GET" action="{{ route('participants.index') }}" class="participant-table-options">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <label for="participants-per-page">Baris:</label>
                    <select id="participants-per-page" name="per_page">
                        @foreach ([10, 25, 50] as $option)
                            <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} baris</option>
                        @endforeach
                    </select>
                    <label for="participants-sort" class="visually-hidden">Urutan peserta</label>
                    <select id="participants-sort" name="sort">
                        <option value="newest" @selected($sort === 'newest')>Terbaru</option>
                        <option value="oldest" @selected($sort === 'oldest')>Terlama</option>
                    </select>
                    <button class="participant-secondary-button" type="submit">Terapkan</button>
                </form>
            </div>

            <x-admin.participant-table :participants="$participants" />

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
        </x-admin.panel>
        <x-admin.participant-create-modal />
    </div>
</x-admin.layout>
