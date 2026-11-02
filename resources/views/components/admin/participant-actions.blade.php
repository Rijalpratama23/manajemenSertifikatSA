@props(['participant'])

<div class="participant-row-actions">
    <a class="participant-action" href="{{ route('participants.show', $participant) }}">Lihat</a>
    <button
        class="participant-action"
        type="button"
        data-open-edit-participant
        data-edit-url="{{ route('participants.update', $participant) }}"
        data-participant-name="{{ $participant->nama }}"
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
        <button class="participant-action participant-action-danger" type="submit">Hapus</button>
    </form>
</div>
