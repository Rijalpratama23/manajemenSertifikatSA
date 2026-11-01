<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - {{ config('app.name', 'Sukarobot Academy') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dashboard-page">
    <main class="dashboard-card">
        <h1>Login berhasil</h1>
        <p>Selamat datang, {{ auth()->user()->nama }}. Anda telah masuk sebagai pengelola internal.</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="submit-button" type="submit">Keluar</button>
        </form>
    </main>
</body>
</html>
