<x-admin.layout title="Arsip Sertifikat">
    <div class="certificate-archive-page">
        <x-admin.flash-message />
        <header class="certificate-archive-header">
            <div>
                <h1>Arsip Sertifikat</h1>
                <p>Daftar seluruh sertifikat yang telah diterbitkan</p>
            </div>
        </header>

        <section class="certificate-archive-filter-panel" aria-labelledby="archive-filter-title">
            <div class="certificate-archive-filter-heading">
                <div>
                    <h2 id="archive-filter-title">Filter dan unduh arsip</h2>
                    <p>Pilih kriteria untuk menyaring daftar atau mengunduh semua PDF yang cocok.</p>
                </div>
            </div>
            <x-admin.certificate-archive.filters
                :search="$search"
                :certificate-types="$certificateTypes"
                :years="$years"
                :selected-type="$selectedType"
                :status="$status"
                :date-filter="$dateFilter"
            />
        </section>

        <x-admin.certificate-archive.summary-stats
            :total="$totalCertificates"
            :active="$activeCertificates"
            :cancelled="$cancelledCertificates"
        />

        <section class="certificate-archive-panel" aria-label="Daftar sertifikat">
            <x-admin.certificate-archive.table :certificates="$certificates" />
            <footer class="certificate-archive-footer">
                <p>
                    @if ($certificates->total() > 0)
                        Menampilkan {{ $certificates->firstItem() }}-{{ $certificates->lastItem() }} dari {{ $certificates->total() }} sertifikat
                    @else
                        Menampilkan 0 sertifikat
                    @endif
                </p>
                {{ $certificates->onEachSide(1)->links() }}
            </footer>
        </section>
    </div>
</x-admin.layout>
