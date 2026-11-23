@props(['participants', 'isCourseView' => false])

<div class="participant-table-scroll">
    <table class="participant-table">
        <thead>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Nama</th>
                <th scope="col">NIS</th>
                <th scope="col">Asal Sekolah/Universitas</th>
                <th scope="col">Jumlah Sertifikat</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($participants as $participant)
                <tr>
                    <td class="participant-row-number">{{ $participants->firstItem() + $loop->index }}</td>
                    <td class="participant-name">{{ $participant->nama }}</td>
                    <td>{{ $participant->nis ?: '—' }}</td>
                    <td>{{ $participant->asal_instansi ?: '—' }}</td>
                    <td><span class="participant-certificate-count">{{ number_format($participant->sertifikat_count, 0, ',', '.') }}</span></td>
                    <td>
                        @if (($participant->source ?? 'peserta') !== 'kursus')
                            <x-admin.participant-actions :participant="$participant" />
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="participant-empty" colspan="6">
                        {{ request('search') ? 'Tidak ada peserta yang cocok dengan pencarian.' : 'Belum ada data peserta.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
