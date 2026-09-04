<x-app-layout>
    <x-slot name="header">
        Mahasiswa Bimbingan
    </x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <p class="text-sm text-siakad-secondary">Daftar mahasiswa di bawah bimbingan akademik Anda</p>
        <div class="flex flex-col md:flex-row items-center gap-3 w-full md:w-auto">
            <!-- Search -->
            <div class="relative w-full md:w-auto">
                <input type="text" id="searchInput" placeholder="Cari nama/NIM..." class="input-saas pl-9 pr-4 py-2 text-sm w-full md:w-48">
                <svg class="w-4 h-4 text-siakad-secondary absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <!-- Filter Angkatan -->
            <select id="angkatanFilter" class="input-saas text-sm py-2 w-full md:w-auto">
                <option value="">Semua Angkatan</option>
                @foreach($angkatanList as $a)
                <option value="{{ $a }}">{{ $a }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card-saas overflow-hidden">
        <div id="tableContainer" class="overflow-x-auto">
            @include('dosen.bimbingan._table')
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const angkatanFilter = document.getElementById('angkatanFilter');
            const tableContainer = document.getElementById('tableContainer');
            let searchTimeout;
            let currentSort = '{{ request("sort", "angkatan") }}';
            let currentDir = '{{ request("dir", "desc") }}';

            function fetchData(page = 1) {
                const search = searchInput.value;
                const angkatan = angkatanFilter.value;
                const url = `{{ route('dosen.bimbingan.index') }}?page=${page}&search=${encodeURIComponent(search)}&angkatan=${encodeURIComponent(angkatan)}&sort=${currentSort}&dir=${currentDir}`;

                tableContainer.style.opacity = '0.5';

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    tableContainer.innerHTML = html;
                    tableContainer.style.opacity = '1';
                    bindPaginationLinks();
                    bindSortableHeaders();
                })
                .catch(err => {
                    console.error(err);
                    tableContainer.style.opacity = '1';
                });
            }

            function bindPaginationLinks() {
                tableContainer.querySelectorAll('.pagination a').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const url = new URL(this.href);
                        const page = url.searchParams.get('page') || 1;
                        fetchData(page);
                    });
                });
            }

            function bindSortableHeaders() {
                tableContainer.querySelectorAll('.sortable-header').forEach(header => {
                    header.addEventListener('click', function() {
                        const sortField = this.dataset.sort;
                        
                        // Toggle direction if same field, otherwise default to asc
                        if (currentSort === sortField) {
                            currentDir = currentDir === 'asc' ? 'desc' : 'asc';
                        } else {
                            currentSort = sortField;
                            currentDir = 'asc';
                        }
                        
                        fetchData(1);
                    });
                });
            }

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => fetchData(), 300);
            });

            angkatanFilter.addEventListener('change', function() {
                fetchData();
            });

            bindPaginationLinks();
            bindSortableHeaders();
        });
    </script>
</x-app-layout>
