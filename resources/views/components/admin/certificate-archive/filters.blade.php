@props(['search', 'certificateTypes', 'years', 'selectedType', 'status', 'dateFilter'])

<form class="certificate-archive-filters" method="GET" action="{{ route('certificate-archive.index') }}" role="search">
    <label class="certificate-archive-filter">
        <span class="visually-hidden">Tanggal terbit</span>
        <select name="tanggal" aria-label="Filter tanggal terbit" data-certificate-archive-filter>
            <option value="all" @selected($dateFilter === 'all')>Tanggal terbit: Semua</option>
            <option value="current_month" @selected($dateFilter === 'current_month')>Bulan ini</option>
            <option value="current_year" @selected($dateFilter === 'current_year')>Tahun ini</option>
            @foreach ($years as $year)
                <option value="{{ $year }}" @selected($dateFilter === $year)>Tahun {{ $year }}</option>
            @endforeach
        </select>
    </label>
    <label class="certificate-archive-search">
        <span class="visually-hidden">Cari nomor sertifikat atau nama peserta</span>
        <input
            type="search"
            name="search"
            value="{{ $search }}"
            placeholder="Cari nomor atau nama peserta"
            maxlength="150"
        >
    </label>
    <label class="certificate-archive-filter">
        <span class="visually-hidden">Jenis sertifikat</span>
        <select name="jenis" aria-label="Filter jenis sertifikat" data-certificate-archive-filter>
            <option value="">Jenis: Semua</option>
            @foreach ($certificateTypes as $type)
                <option value="{{ $type->id_jenis }}" @selected((string) $selectedType === (string) $type->id_jenis)>
                    {{ $type->nama_jenis }}
                </option>
            @endforeach
        </select>
    </label>
    <label class="certificate-archive-filter">
        <span class="visually-hidden">Status sertifikat</span>
        <select name="status" aria-label="Filter status sertifikat" data-certificate-archive-filter>
            <option value="all" @selected($status === 'all')>Status: Semua</option>
            <option value="aktif" @selected($status === 'aktif')>Aktif</option>
            <option value="dibatalkan" @selected($status === 'dibatalkan')>Dibatalkan</option>
        </select>
    </label>
    <button class="certificate-archive-filter-submit" type="submit">Terapkan</button>
</form>
