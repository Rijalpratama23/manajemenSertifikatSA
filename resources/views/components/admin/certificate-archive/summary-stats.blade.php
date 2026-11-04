@props(['total', 'active', 'cancelled'])

<div class="certificate-archive-stats" aria-label="Ringkasan arsip sertifikat">
    <span>Total <strong>{{ number_format($total, 0, ',', '.') }}</strong></span>
    <span>Aktif <strong>{{ number_format($active, 0, ',', '.') }}</strong></span>
    <span>Dibatalkan <strong>{{ number_format($cancelled, 0, ',', '.') }}</strong></span>
</div>
