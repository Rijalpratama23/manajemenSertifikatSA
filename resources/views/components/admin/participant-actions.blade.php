@props(['participant'])

<div class="participant-row-actions">
    <a
        class="participant-action"
        href="{{ route('participants.show', ['peserta' => $participant, ...(request()->filled('jenis') ? ['jenis' => request('jenis')] : [])]) }}"
    >Lihat</a>
    <button
        class="participant-action"
        type="button"
        data-open-edit-participant
        data-edit-url="{{ route('participants.update', $participant) }}"
        data-participant-name="{{ $participant->nama }}"
        data-participant-nis="{{ $participant->nis }}"
        data-participant-birth-place="{{ $participant->tempat_lahir }}"
        data-participant-birth-date="{{ $participant->tanggal_lahir?->toDateString() }}"
        data-participant-email="{{ $participant->email }}"
        data-participant-institution="{{ $participant->asal_instansi }}"
    >Ubah</button>
    <form
        method="POST"
        action="{{ route('participants.destroy', $participant) }}"
        data-confirm="Hapus data peserta {{ $participant->nama }}?"
    >
        @csrf
        @method('DELETE')
        @if (request()->filled('jenis'))
            <input type="hidden" name="jenis" value="{{ request('jenis') }}">
        @endif
        <button class="participant-action participant-action-danger" type="submit">Hapus</button>
    </form>
</div>
