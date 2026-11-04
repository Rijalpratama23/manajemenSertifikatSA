@props(['certificates'])

<div class="certificate-archive-table-scroll">
    <table class="certificate-archive-table">
        <thead>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Nomor Sertifikat</th>
                <th scope="col">Peserta</th>
                <th scope="col">Jenis</th>
                <th scope="col">Periode</th>
                <th scope="col">Terbit</th>
                <th scope="col">Status</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($certificates as $certificate)
                <tr>
                    <td>{{ $certificates->firstItem() + $loop->index }}</td>
                    <td class="certificate-archive-number">{{ $certificate->nomor_sertifikat }}</td>
                    <td>{{ $certificate->peserta->nama }}</td>
                    <td><span class="certificate-archive-type">{{ $certificate->jenis->nama_jenis }}</span></td>
                    <td>
                        @if ($certificate->periode_mulai && $certificate->periode_selesai)
                            {{ $certificate->periode_mulai->locale('id')->translatedFormat('j M') }} - {{ $certificate->periode_selesai->locale('id')->translatedFormat('j M Y') }}
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $certificate->tanggal_terbit->locale('id')->translatedFormat('j M Y') }}</td>
                    <td>
                        <span class="certificate-archive-status {{ $certificate->isAktif() ? 'is-active' : 'is-cancelled' }}">
                            {{ $certificate->isAktif() ? 'Aktif' : 'Dibatalkan' }}
                        </span>
                    </td>
                    <td>
                        <div class="certificate-archive-row-actions">
                            <a class="certificate-archive-action" href="{{ route('certificate-archive.show', $certificate) }}">Lihat</a>
                            @if ($certificate->berkas_pdf)
                                <a class="certificate-archive-action" href="{{ route('certificate-issues.download', $certificate) }}">Unduh</a>
                            @endif
                            @if ($certificate->isAktif())
                                <form
                                    method="POST"
                                    action="{{ route('certificate-archive.cancel', $certificate) }}"
                                    data-confirm
                                    data-confirm-style="template"
                                    data-confirm-title="Batalkan sertifikat ini?"
                                    data-confirm-message="Sertifikat {{ $certificate->nomor_sertifikat }} akan berubah menjadi tidak aktif. Sertifikat dapat diterbitkan ulang dengan nomor baru."
                                    data-confirm-label="Batalkan"
                                    data-confirm-tone="danger"
                                    data-confirm-icon="warning"
                                >
                                    @csrf
                                    <button class="certificate-archive-action is-danger" type="submit">Batalkan</button>
                                </form>
                            @else
                                <a
                                    class="certificate-archive-action is-reissue"
                                    href="{{ route('certificate-issues.create', ['ulangi' => $certificate->id_sertifikat]) }}"
                                >Terbitkan ulang</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="certificate-archive-empty" colspan="8">
                        {{ request()->query() ? 'Tidak ada sertifikat yang sesuai dengan pencarian atau filter.' : 'Belum ada sertifikat yang diterbitkan.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
