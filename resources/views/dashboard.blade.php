<x-admin.layout title="Dashboard">
    <div class="dashboard-content">
        <header class="dashboard-header">
            <div>
                <h1>Dashboard</h1>
                <p>Pengelolaan sertifikat internal Sukarobot Academy</p>
            </div>
            <div class="dashboard-actions" aria-label="Aksi dashboard">
                <form class="dashboard-period-form" method="GET" action="{{ route('dashboard') }}" data-period-picker>
                    <label class="dashboard-period" for="dashboard-period">
                        <button
                            class="dashboard-calendar-trigger"
                            type="button"
                            aria-label="Buka pemilih bulan"
                            aria-controls="dashboard-period"
                        >
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <rect x="4" y="6" width="16" height="15" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M8 3.5v5M16 3.5v5M4 10h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </button>
                        <span>Periode:</span>
                        <span class="visually-hidden">Pilih periode dashboard</span>
                        <input
                            id="dashboard-period"
                            name="period"
                            type="month"
                            value="{{ $selectedPeriod }}"
                            aria-label="Pilih periode dashboard"
                            required
                        >
                    </label>
                    <button class="dashboard-period-submit" type="submit">Terapkan</button>
                </form>
                <button class="dashboard-action-button" type="button" disabled title="Fitur pencarian akan tersedia berikutnya">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="10.8" cy="10.8" r="6.3" stroke="currentColor" stroke-width="1.8"/>
                        <path d="m15.5 15.5 4.2 4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    Cari sertifikat
                </button>
                <button class="dashboard-action-button dashboard-action-primary" type="button" disabled title="Fitur unduh arsip akan tersedia berikutnya">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 3.5v11m0 0 4-4m-4 4-4-4M4.5 15v4.5h15V15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Unduh Arsip
                </button>
            </div>
        </header>

        <section class="dashboard-statistics" aria-label="Statistik sertifikat">
            @foreach ($statistics as $statistic)
                <x-admin.stat-card
                    :label="$statistic['label']"
                    :value="$statistic['value']"
                    :detail="$statistic['detail']"
                    :tone="$statistic['tone']"
                >
                    <x-slot:icon>
                        @if ($statistic['icon'] === 'document')
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M7 3.5h7l4 4v13H7v-17Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <path d="M14 3.8v4h3.8M10 12h5m-5 3.5h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        @elseif ($statistic['icon'] === 'template')
                            <svg viewBox="0 0 24 24" fill="none">
                                <rect x="4" y="4" width="16" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M4 9h16M9 9v11" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                        @else
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M7 8V4h10v4m-10 9H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2m-10-4h10v7H7v-7Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <circle cx="17.5" cy="10.5" r=".8" fill="currentColor"/>
                            </svg>
                        @endif
                    </x-slot:icon>
                </x-admin.stat-card>
            @endforeach
        </section>

        <div class="dashboard-lower-grid">
            <x-admin.panel
                title="Aktivitas Terbaru"
                subtitle="Sertifikat terakhir yang diterbitkan."
                class="activity-panel"
            >
                @if ($recentCertificates->isNotEmpty())
                    <ul class="activity-list">
                        @foreach ($recentCertificates as $certificate)
                            <x-admin.activity-item
                                title="Sertifikat diterbitkan"
                                :description="($certificate->peserta?->nama ?? 'Peserta tidak tersedia') . ' · ' . ($certificate->jenis?->nama_jenis ?? 'Jenis tidak tersedia')"
                                :time="$certificate->tanggal_terbit->format('d/m/Y')"
                                :tone="$loop->odd ? 'teal' : 'orange'"
                            >
                                <x-slot:icon>
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M7 3.5h7l4 4v13H7v-17Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                        <path d="M14 3.8v4h3.8M10 12h5m-5 3.5h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </x-slot:icon>
                            </x-admin.activity-item>
                        @endforeach
                    </ul>
                @else
                    <p class="dashboard-empty-state">Belum ada sertifikat yang diterbitkan.</p>
                @endif
            </x-admin.panel>

            <div class="dashboard-side-panels">
                <x-admin.panel
                    title="Ringkasan Kegiatan"
                    subtitle="Data pengelolaan sertifikat saat ini."
                >
                    <dl class="summary-list">
                        @foreach ($summary as $item)
                            <div>
                                <dt>{{ $item['label'] }}</dt>
                                <dd>{{ number_format($item['value'], 0, ',', '.') }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </x-admin.panel>

                <x-admin.panel
                    title="Status Pengelolaan"
                    subtitle="Persentase data aktif atau tersedia."
                >
                    <div class="status-list">
                        @foreach ($statuses as $status)
                            <x-admin.status-row
                                :label="$status['label']"
                                :percentage="$status['percentage']"
                                :tone="$status['tone']"
                            />
                        @endforeach
                    </div>
                </x-admin.panel>
            </div>
        </div>
    </div>
</x-admin.layout>
