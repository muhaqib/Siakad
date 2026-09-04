<x-app-layout>
    <x-slot name="header">
        Approval KRS Mahasiswa Bimbingan
    </x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <p class="text-sm text-siakad-secondary">Daftar KRS mahasiswa bimbingan yang menunggu persetujuan</p>
        <div class="flex flex-col md:flex-row items-center gap-3 w-full md:w-auto">
            <!-- Search -->
            <div class="relative w-full md:w-auto">
                <input type="text" id="searchInput" placeholder="Cari nama/NIM..." class="input-saas pl-9 pr-4 py-2 text-sm w-full md:w-48">
                <svg class="w-4 h-4 text-siakad-secondary absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <!-- Filter Status -->
            <select id="statusFilter" class="input-saas text-sm py-2 w-full md:w-auto">
                <option value="pending" {{ request('status', 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Semua Status</option>
            </select>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card-saas overflow-hidden">
        <div id="tableContainer" class="overflow-x-auto">
            @include('dosen.bimbingan._krs-table')
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-siakad-dark rounded-xl w-full max-w-md shadow-xl">
            <div class="px-6 py-4 border-b border-siakad-light dark:border-siakad-light/20">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white">Tolak KRS</h3>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="p-6">
                    <label class="block text-sm font-medium text-siakad-dark dark:text-white mb-2">Alasan Penolakan</label>
                    <textarea name="catatan" rows="4" class="input-saas w-full resize-none" placeholder="Masukkan alasan mengapa KRS ditolak... (opsional)"></textarea>
                    <p class="text-xs text-siakad-secondary mt-2">Catatan ini akan dilihat oleh mahasiswa sebagai alasan penolakan.</p>
                </div>
                <div class="px-6 py-4 border-t border-siakad-light dark:border-siakad-light/20 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeRejectModal()" class="btn-ghost-saas px-4 py-2 rounded-lg text-sm">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition">Tolak KRS</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const tableContainer = document.getElementById('tableContainer');
            let searchTimeout;
            let currentSort = '{{ request("sort", "updated_at") }}';
            let currentDir = '{{ request("dir", "desc") }}';

            function fetchData(page = 1) {
                const search = searchInput.value;
                const status = statusFilter.value;
                const url = `{{ route('dosen.bimbingan.krs-approval') }}?page=${page}&search=${encodeURIComponent(search)}&sort=${currentSort}&dir=${currentDir}&status=${status}`;

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

            statusFilter.addEventListener('change', function() {
                fetchData();
            });

            bindPaginationLinks();
            bindSortableHeaders();
        });

        function openRejectModal(url) {
            document.getElementById('rejectForm').action = url;
            document.getElementById('rejectModal').classList.remove('hidden');
        }
        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
