@props(['jenisSertifikat'])

<div class="participant-table-scroll">
    <table class="participant-table certificate-type-table">
        <thead>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Kode</th>
                <th scope="col">Nama Jenis</th>
                <th scope="col">Template Aktif</th>
                <th scope="col">Jumlah Sertifikat</th>
                <th scope="col">Status</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($jenisSertifikat as $jenis)
                <tr>
                    <td class="participant-row-number">{{ $jenisSertifikat->firstItem() + $loop->index }}</td>
                    <td class="participant-name">{{ $jenis->kode }}</td>
                    <td>{{ $jenis->nama_jenis }}</td>
                    <td>
                        @if ($jenis->template->isNotEmpty())
                            {{ $jenis->template->pluck('nama_template')->join(', ') }}
                        @else
                            <span class="certificate-type-no-template">Belum Ada Template</span>
                        @endif
                    </td>
                    <td><span class="participant-certificate-count">{{ number_format($jenis->sertifikat_count, 0, ',', '.') }}</span></td>
                    <td>
                        <span class="certificate-type-status {{ $jenis->aktif ? 'is-active' : 'is-inactive' }}">
                            {{ $jenis->aktif ? 'Aktif' : 'Non-Aktif' }}
                        </span>
                    </td>
                    <td><x-admin.certificate-type-actions :jenis="$jenis" /></td>
                </tr>
            @empty
                <tr>
                    <td class="participant-empty" colspan="7">
                        {{ request('search') ? 'Tidak ada jenis sertifikat yang cocok dengan pencarian.' : 'Belum ada jenis sertifikat.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
