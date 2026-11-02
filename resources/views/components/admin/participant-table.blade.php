@props(['participants'])

<div class="participant-table-scroll">
    <table class="participant-table">
        <thead>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Nama</th>
                <th scope="col">Email</th>
                <th scope="col">Asal Sekolah/Universitas</th>
                <th scope="col">Jumlah Sertifikat</th>
                <th scope="col">Ditambahkan</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($participants as $participant)
                <tr>
                    <td class="participant-row-number">{{ $participants->firstItem() + $loop->index }}</td>
                    <td class="participant-name">{{ $participant->nama }}</td>
                    <td>{{ $participant->email }}</td>
                    <td>{{ $participant->asal_instansi ?: '—' }}</td>
                    <td><span class="participant-certificate-count">{{ number_format($participant->sertifikat_count, 0, ',', '.') }}</span></td>
                    <td class="participant-date">{{ $participant->dibuat_pada?->format('d M Y') ?? '—' }}</td>
                    <td>
                        <x-admin.participant-actions :participant="$participant" />
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="participant-empty" colspan="7">
                        {{ request('search') ? 'Tidak ada peserta yang cocok dengan pencarian.' : 'Belum ada data peserta.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
