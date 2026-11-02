@props(['label', 'percentage', 'tone' => 'teal'])

<div class="status-row">
    <div class="status-label">
        <span>{{ $label }}</span>
        <strong class="status-value-{{ $tone }}">{{ $percentage }}%</strong>
    </div>
    <div
        class="status-track"
        role="progressbar"
        aria-label="{{ $label }}"
        aria-valuemin="0"
        aria-valuemax="100"
        aria-valuenow="{{ $percentage }}"
    >
        <span class="status-fill status-fill-{{ $tone }}" style="width: {{ $percentage }}%"></span>
    </div>
</div>
