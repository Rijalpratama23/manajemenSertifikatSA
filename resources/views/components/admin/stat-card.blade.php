@props(['label', 'value', 'detail', 'tone' => 'teal'])

<article class="stat-card">
    <div class="stat-card-heading">
        <span class="stat-icon stat-icon-{{ $tone }}" aria-hidden="true">
            {{ $icon ?? '' }}
        </span>
        <h2>{{ $label }}</h2>
    </div>
    <p class="stat-value">{{ number_format($value, 0, ',', '.') }}</p>
    <p class="stat-detail stat-detail-{{ $tone }}">{{ $detail }}</p>
</article>
