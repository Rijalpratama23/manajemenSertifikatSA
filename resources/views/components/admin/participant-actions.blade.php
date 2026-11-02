@props(['participant'])

<div class="participant-row-actions">
    <a class="participant-action" href="{{ route('participants.show', $participant) }}">Lihat</a>
    <a class="participant-action" href="{{ route('participants.edit', $participant) }}">Ubah</a>
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
