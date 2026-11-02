<dialog class="delete-confirmation" aria-labelledby="delete-confirmation-title" data-delete-confirmation>
    <span class="delete-confirmation-step" data-confirmation-step hidden></span>
    <button class="delete-confirmation-close" type="button" aria-label="Tutup konfirmasi" data-cancel-delete>
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
    </button>
    <div class="delete-confirmation-content">
        <span class="delete-confirmation-icon" data-confirmation-icon aria-hidden="true">!</span>
        <h2 id="delete-confirmation-title">Yakin Ingin Menghapus?</h2>
        <p id="delete-confirmation-message">Data akan dihapus secara permanen.</p>
        <div class="delete-confirmation-actions">
            <button class="delete-confirmation-yes" type="button" data-confirm-delete>Ya</button>
            <button class="delete-confirmation-no" type="button" data-cancel-delete>Tidak</button>
        </div>
    </div>
</dialog>
