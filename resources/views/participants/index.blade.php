<x-admin.layout title="Data Peserta">
    <div class="participants-content">
        <x-admin.flash-message />

        <header class="dashboard-header participants-header">
            <div class="participants-heading">
                <h1>Data Peserta</h1>
                <p>Pengolahan Data Peserta</p>
            </div>
            <div class="participants-header-actions">
                <form class="participant-search" method="GET" action="{{ route('participants.index') }}" role="search" autocomplete="off" data-participant-search-form>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="10.8" cy="10.8" r="6.3" stroke="currentColor" stroke-width="1.8"/>
                        <path d="m15.5 15.5 4.2 4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari peserta"
                        aria-label="Cari nama, NIS, email, atau instansi peserta"
                        autocomplete="off"
                        aria-autocomplete="list"
                        aria-controls="participant-search-suggestions"
                        aria-expanded="false"
                        data-participant-search
                    >
                    <div class="certificate-type-search-suggestions" id="participant-search-suggestions" role="listbox" data-participant-search-suggestions hidden>
                        @foreach ($participantSearchSuggestions as $suggestion)
                            <button type="button" role="option" aria-selected="false" data-participant-search-suggestion>{{ $suggestion }}</button>
                        @endforeach
                        <p class="certificate-type-search-error" role="alert" data-participant-search-error hidden></p>
                    </div>
                    <input type="hidden" name="jenis" value="{{ $selectedType }}">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                </form>
                <div class="participants-action-group">
                    <button class="participant-primary-button" type="button" data-open-participant-modal>Tambah Peserta</button>
                    <a class="participant-secondary-button" href="{{ route('participants.template.download') }}">Unduh Template</a>
                </div>
                <x-admin.participant-import />
            </div>
        </header>

        <x-admin.panel
            :title="$selectedCertificateTypeName ? 'Peserta ' . $selectedCertificateTypeName : 'Tabel Peserta'"
            :subtitle="'Menampilkan ' . $participants->count() . ' dari ' . $participants->total() . ' peserta'"
            class="participants-panel"
        >
            <div class="participant-table-toolbar">
                <form method="GET" action="{{ route('participants.index') }}" class="participant-table-options">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    @if ($selectedType !== '')
                        <input type="hidden" name="jenis" value="{{ $selectedType }}">
                    @endif
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

            <div data-participant-table>
                <x-admin.participant-table :participants="$participants" :is-course-view="$isCourseView" />
            </div>

            <div data-participant-footer>
                @include('participants.partials.pagination', ['participants' => $participants])
            </div>
        </x-admin.panel>
        <x-admin.participant-create-modal />
        <x-admin.participant-edit-modal :edit-participant="$editParticipant" />
    </div>
</x-admin.layout>
