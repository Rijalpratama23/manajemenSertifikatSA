<x-admin.layout title="Template Sertifikat">
    <div class="certificate-templates-content">
        <x-admin.flash-message />

        <header class="dashboard-header certificate-templates-header">
            <div>
                <h1>Template Sertifikat</h1>
                <p>Kelola desain template untuk tiap jenis sertifikat</p>
            </div>
            <form class="participant-search" method="GET" action="{{ route('certificate-templates.index') }}" role="search">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="10.8" cy="10.8" r="6.3" stroke="currentColor" stroke-width="1.8"/>
                    <path d="m15.5 15.5 4.2 4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <input type="search" name="search" value="{{ $search }}" placeholder="Cari template" aria-label="Cari template">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="hidden" name="jenis" value="{{ $jenisId }}">
            </form>
        </header>

        @if ($jenisTanpaTemplateAktif->isNotEmpty())
            <aside class="certificate-template-warning" role="status">
                <span>
                    {{ $jenisTanpaTemplateAktif->count() }} jenis aktif belum memiliki template aktif:
                    <strong>{{ $jenisTanpaTemplateAktif->pluck('nama_jenis')->join(', ') }}</strong>.
                </span>
                <a
                    class="certificate-template-warning-link"
                    href="{{ route('certificate-templates.create', ['jenis' => $jenisTanpaTemplateAktif->first()->id_jenis]) }}"
                >Tambah template</a>
            </aside>
        @endif

        <section class="certificate-template-toolbar" aria-label="Aksi dan filter template">
            <a class="participant-primary-button certificate-template-add" href="{{ route('certificate-templates.create') }}">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Tambah Template
            </a>
            <form method="GET" action="{{ route('certificate-templates.index') }}" class="certificate-template-filters">
                <input type="hidden" name="search" value="{{ $search }}">
                <label class="certificate-template-select">
                    <span class="visually-hidden">Filter status template</span>
                    <select name="status" data-template-filter>
                        <option value="all" @selected($status === 'all')>Status: Semua</option>
                        <option value="active" @selected($status === 'active')>Status: Aktif</option>
                        <option value="inactive" @selected($status === 'inactive')>Status: Nonaktif</option>
                    </select>
                </label>
                <label class="certificate-template-select">
                    <span class="visually-hidden">Filter jenis sertifikat</span>
                    <select name="jenis" data-template-filter>
                        <option value="">Semua jenis</option>
                        @foreach ($jenisSertifikat as $jenis)
                            <option value="{{ $jenis->id_jenis }}" @selected((string) $jenisId === (string) $jenis->id_jenis)>
                                {{ $jenis->nama_jenis }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </form>
            <p class="certificate-template-count">
                Menampilkan {{ $templates->firstItem() ?? 0 }}–{{ $templates->lastItem() ?? 0 }}
                dari {{ $templates->total() }} template
            </p>
        </section>

        @if ($templates->isNotEmpty())
            <div class="certificate-template-grid">
                @foreach ($templates as $template)
                    <x-admin.certificate-template-card :template="$template" />
                @endforeach
            </div>
            <footer class="certificate-template-pagination">
                {{ $templates->onEachSide(1)->links() }}
            </footer>
        @else
            <div class="certificate-template-empty">
                <strong>Template belum tersedia</strong>
                <p>{{ $search !== '' ? 'Tidak ada template yang cocok dengan pencarian atau filter.' : 'Tambahkan template desain untuk mulai mengelola sertifikat.' }}</p>
                @if ($search === '')
                    <a class="participant-primary-button" href="{{ route('certificate-templates.create') }}">Tambah Template</a>
                @endif
            </div>
        @endif

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
        <dialog class="certificate-template-preview" aria-labelledby="certificate-template-preview-title" data-template-preview>
            <button class="certificate-template-preview-close" type="button" aria-label="Tutup pratinjau" data-close-template-preview>&times;</button>
            <h2 id="certificate-template-preview-title" data-preview-title>Pratinjau Template</h2>
            <img src="" alt="" data-preview-image hidden>
            <iframe title="Pratinjau berkas PDF" data-preview-pdf hidden></iframe>
        </dialog>
    </div>
</x-admin.layout>
