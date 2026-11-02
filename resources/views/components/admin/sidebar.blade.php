<aside class="admin-sidebar" aria-label="Navigasi admin">
    <a class="admin-brand" href="{{ route('dashboard') }}" aria-label="Sukarobot Academy">
        <img src="{{ asset('images/logo.png') }}" alt="Logo Sukarobot Academy">
    </a>

    <nav class="admin-navigation" aria-label="Menu utama">
        <a
            class="admin-nav-item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
            href="{{ route('dashboard') }}"
            @if (request()->routeIs('dashboard')) aria-current="page" @endif
        >
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M3.5 10.5 12 3.8l8.5 6.7v9a1 1 0 0 1-1 1h-5.2v-6.2H9.7v6.2H4.5a1 1 0 0 1-1-1v-9Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            </svg>
            <span>Dashboard</span>
        </a>
        <a
            class="admin-nav-item {{ request()->routeIs('participants.*') ? 'is-active' : '' }}"
            href="{{ route('participants.index') }}"
            @if (request()->routeIs('participants.*')) aria-current="page" @endif
        >
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="9" cy="8" r="3" stroke="currentColor" stroke-width="1.8"/>
                <path d="M3.5 20v-1.5a5.5 5.5 0 0 1 11 0V20M16 5.5a3 3 0 0 1 0 5.8m2 3.2a5 5 0 0 1 2.5 4.3V20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
            <span>Data Peserta</span>
        </a>
        <span class="admin-nav-item is-unavailable" aria-disabled="true">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M6 3.5h8l4.5 4.6v12.4H6V3.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                <path d="M14 3.8v4.7h4.2M9 12h6m-6 3.5h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
            <span>Jenis Sertifikat</span>
        </span>
        <span class="admin-nav-item is-unavailable" aria-disabled="true">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M4 4.5h16v15H4v-15Zm0 5h16M9 9.5v10" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            </svg>
            <span>Template Sertifikat</span>
        </span>
        <span class="admin-nav-item is-unavailable" aria-disabled="true">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M7 8V4h10v4m-10 9H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2m-10-4h10v7H7v-7Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                <circle cx="17.5" cy="10.5" r=".8" fill="currentColor"/>
            </svg>
            <span>Terbitkan Sertifikat</span>
        </span>
        <span class="admin-nav-item is-unavailable" aria-disabled="true">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M4 8.5h16v12H4v-12Zm-1.5-4h19v4h-19v-4Zm9.5 8v4" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            </svg>
            <span>Arsip Sertifikat</span>
        </span>
    </nav>

    <div class="admin-sidebar-bottom">
        <div class="admin-profile">
            <span class="admin-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->nama, 0, 2)) }}</span>
            <span class="admin-profile-text">
                <strong>{{ auth()->user()->nama }}</strong>
                <small>Admin Pengelola</small>
            </span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="admin-logout" type="submit">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M10 4H5v16h5m5-4 4-4-4-4m4 4H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>
