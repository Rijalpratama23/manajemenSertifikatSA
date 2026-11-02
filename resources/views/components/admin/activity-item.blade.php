@props(['title', 'description', 'time', 'tone' => 'teal'])

<li class="activity-item">
    <span class="activity-icon activity-icon-{{ $tone }}" aria-hidden="true">
        {{ $icon ?? '' }}
    </span>
    <span class="activity-copy">
        <strong>{{ $title }}</strong>
        <small>{{ $description }}</small>
    </span>
    <time class="activity-time">{{ $time }}</time>
</li>
