@php($notice = session('auth_notice'))

@if (is_array($notice) && isset($notice['message']))
    @php($type = in_array($notice['type'] ?? null, ['success', 'error'], true) ? $notice['type'] : 'success')
    <div
        class="app-toast is-{{ $type }}"
        role="{{ $type === 'error' ? 'alert' : 'status' }}"
        aria-live="{{ $type === 'error' ? 'assertive' : 'polite' }}"
        data-app-toast
    >
        <span class="app-toast-icon" aria-hidden="true">
            @if ($type === 'success')
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="m5 12.5 4.5 4.5L19 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            @else
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M12 8v5m0 3h.01M10.3 3.9 2.8 17a2 2 0 0 0 1.7 3h15a2 2 0 0 0 1.7-3l-7.5-13.1a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            @endif
        </span>
        <span class="app-toast-message">{{ $notice['message'] }}</span>
        <button class="app-toast-close" type="button" aria-label="Tutup pemberitahuan" data-dismiss-toast>&times;</button>
    </div>
@endif
