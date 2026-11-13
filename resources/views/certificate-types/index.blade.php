<x-admin.layout title="Jenis Sertifikat">
    <div class="participants-content">
        <x-admin.flash-message />

        <header class="dashboard-header certificate-types-header">
            <div>
                <h1>Jenis Sertifikat</h1>
                <p>Pilih dan kelola jenis sertifikat</p>
            </div>
            <div class="certificate-types-header-actions">
                <form method="GET" action="{{ route('certificate-types.index') }}" class="certificate-type-filter">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <button
                        class="certificate-type-filter-trigger"
                        type="button"
                        aria-label="Filter jenis sertifikat"
                        aria-expanded="false"
                        aria-controls="certificate-type-filter-menu"
                        data-certificate-type-filter-trigger
                    >
                        {{ ['all' => 'Semua', 'active' => 'Aktif', 'inactive' => 'Nonaktif'][$status] }}
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m7 10 5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div class="certificate-type-filter-menu" id="certificate-type-filter-menu" data-certificate-type-filter-menu hidden>
                        <button
                            type="submit"
                            name="status"
                            value="all"
                            @if ($status === 'all') aria-current="true" @endif
                        >Semua</button>
                        <button
                            type="submit"
                            name="status"
                            value="active"
                            @if ($status === 'active') aria-current="true" @endif
                        >Aktif</button>
                        <button
                            type="submit"
                            name="status"
                            value="inactive"
                            @if ($status === 'inactive') aria-current="true" @endif
                        >Nonaktif</button>
                    </div>
                </form>
                <form class="participant-search" method="GET" action="{{ route('certificate-types.index') }}" role="search">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="10.8" cy="10.8" r="6.3" stroke="currentColor" stroke-width="1.8"/>
                        <path d="m15.5 15.5 4.2 4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari jenis sertifikat"
                        aria-label="Cari kode atau nama jenis sertifikat"
                    >
                    <input type="hidden" name="status" value="{{ $status }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                </form>
                <button class="participant-primary-button" type="button" data-open-certificate-type-create>Tambah Jenis</button>
            </div>
        </header>

        <x-admin.panel
            title="Jenis Sertifikat"
            :subtitle="'Menampilkan ' . $jenisSertifikat->count() . ' dari ' . $jenisSertifikat->total() . ' jenis sertifikat'"
            class="participants-panel"
        >
            <div class="participant-table-toolbar">
                <form method="GET" action="{{ route('certificate-types.index') }}" class="participant-table-options">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <label for="certificate-types-per-page">Baris:</label>
                    <select id="certificate-types-per-page" name="per_page">
                        @foreach ([10, 25, 50] as $option)
                            <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }} baris</option>
                        @endforeach
                    </select>
                    <label for="certificate-types-sort" class="visually-hidden">Urutan nama jenis sertifikat</label>
                    <select id="certificate-types-sort" name="sort">
                        <option value="asc" @selected($sort === 'asc')>A-Z</option>
                        <option value="desc" @selected($sort === 'desc')>Z-A</option>
                    </select>
                    <button class="participant-secondary-button certificate-type-apply-button" type="submit">Terapkan</button>
                </form>
            </div>

            <x-admin.certificate-type-table :jenis-sertifikat="$jenisSertifikat" />

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
        </x-admin.panel>

        <x-admin.certificate-type-modal
            :edit-jenis="$editJenis"
            :should-open="$errors->hasAny(['kode', 'nama_jenis'])"
        />
    </div>
</x-admin.layout>
