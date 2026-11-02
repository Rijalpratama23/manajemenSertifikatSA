@props(['title'])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5fafb">
    <title>{{ $title }} - {{ config('app.name', 'Sukarobot Academy') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-page">
    <x-admin.sidebar />
    <main class="admin-main">
        {{ $slot }}
    </main>
    <x-admin.delete-confirmation />
</body>
</html>
