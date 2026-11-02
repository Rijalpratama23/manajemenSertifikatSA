@props(['title', 'subtitle' => null, 'class' => ''])

<section {{ $attributes->class(['admin-panel', $class]) }}>
    <header class="panel-heading">
        <h2>{{ $title }}</h2>
        @if ($subtitle)
            <p>{{ $subtitle }}</p>
        @endif
    </header>
    {{ $slot }}
</section>
