@props([
    'title',
    'themeColor' => null,
    'bodyClass' => null,
    'bodyStyle' => null,
])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if ($themeColor)
        <meta name="theme-color" content="{{ $themeColor }}">
    @endif
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body @if ($bodyClass) class="{{ $bodyClass }}" @endif @if ($bodyStyle) style="{{ $bodyStyle }}" @endif>
    <x-ui.flash-toast />
    {{ $slot }}
</body>
</html>
