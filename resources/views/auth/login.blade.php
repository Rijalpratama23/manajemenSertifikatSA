<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#eaf1f1">
    <title>Masuk - {{ config('app.name', 'Sukarobot Academy') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page" style="--login-background-image: url('{{ asset('images/login-ilustrationss.png') }}')">
    <main class="login-layout">
        <section class="login-panel" aria-labelledby="welcome-title">
            <div class="login-card">
                <header class="login-header">
                    <a class="brand" href="{{ route('login') }}" aria-label="Sukarobot Academy">
                        <img
                            src="{{ asset('images/logo.png') }}"
                            alt="Logo Sukarobot Academy"
                            class="brand-logo"
                        >
                    </a>
                    <h1 id="welcome-title">Selamat Datang!</h1>
                    <p class="login-intro">Sistem Pengelola Sertifikat Internal<br>Sukarobot Academy</p>
                </header>

                <form class="login-form" method="POST" action="{{ route('login.store') }}" autocomplete="off">
                    @csrf

                    <div class="form-field">
                        <label for="username">Username</label>
                        <div class="input-wrap @error('username') input-wrap-error @enderror">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M5.5 20v-1.6a6.5 6.5 0 0 1 13 0V20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                            <input
                                id="username"
                                name="username"
                                type="text"
                                placeholder="Masukan Username..."
                                value="{{ old('username') }}"
                                autocomplete="off"
                                maxlength="50"
                                required
                                aria-invalid="{{ $errors->has('username') ? 'true' : 'false' }}"
                                aria-describedby="{{ $errors->has('username') ? 'username-error' : '' }}"
                            >
                        </div>
                        @error('username')
                            <p class="field-error" id="username-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="password">Password</label>
                        <div class="input-wrap @error('password') input-wrap-error @enderror">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <rect x="5" y="10" width="14" height="11" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                <circle cx="12" cy="15.5" r="1.2" fill="currentColor"/>
                            </svg>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                placeholder="Masukan Password..."
                                autocomplete="off"
                                required
                                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                aria-describedby="{{ $errors->has('password') ? 'password-error' : '' }}"
                            >
                            <button class="password-toggle" type="button" aria-label="Tampilkan password" aria-pressed="false" data-password-toggle>
                                <svg class="eye-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <g data-eye-visible>
                                        <path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.8"/>
                                        <circle cx="12" cy="12" r="2.7" stroke="currentColor" stroke-width="1.8"/>
                                    </g>
                                    <g data-eye-hidden>
                                        <path d="M3 12s3.2-6 9-6c2.1 0 3.9.7 5.3 1.6M21 12s-3.2 6-9 6c-2.1 0-3.9-.7-5.3-1.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="m4 4 16 16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </g>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="field-error" id="password-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="forgot-password-row">
                        <button class="forgot-password" type="button" data-forgot-password>Lupa Password?</button>
                    </div>
                    <p class="recovery-message" data-recovery-message role="status" hidden>
                        Silakan hubungi administrator untuk mengatur ulang password Anda.
                    </p>

                    <button class="submit-button" type="submit">Masuk</button>
                </form>

                <p class="login-footer">Akses Khusus Pengelola Internal Sukarobot</p>
            </div>
        </section>
    </main>
</body>
</html>
