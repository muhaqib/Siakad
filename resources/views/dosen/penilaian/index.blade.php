<x-app-layout>
    <x-slot name="header">
        Input Nilai Mahasiswa
    </x-slot>

    <!-- Search & Filter Bar -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-2">
            <div>
                <p class="text-sm text-siakad-secondary">Kelola nilai untuk <span id="totalKelas" class="font-semibold text-siakad-primary dark:text-white">{{ $kelasAjar->count() }}</span> kelas</p>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
            <!-- Search -->
            <div class="relative w-full sm:w-auto">
                <input type="text" id="searchInput" placeholder="Cari mata kuliah..." class="input-saas pl-9 pr-4 py-2 text-sm w-full sm:w-56">
                <svg class="w-4 h-4 text-siakad-secondary absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <!-- Filter Semester -->
            <select id="semesterFilter" class="input-saas text-sm py-2 w-full sm:w-auto">
                <option value="">Semua Semester</option>
                @foreach($semesterList as $sem)
                <option value="{{ $sem }}">Semester {{ $sem }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if($kelasAjar->isEmpty() && !request('search') && !request('semester'))
    <div class="card-saas p-12 text-center">
        <div class="w-20 h-20 rounded-full bg-siakad-light dark:bg-gray-700 flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-siakad-secondary dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
        </div>
        <h3 class="text-xl font-bold text-siakad-dark dark:text-white mb-2">Belum Ada Kelas</h3>
        <p class="text-siakad-secondary dark:text-gray-400">Anda belum memiliki kelas yang diampu untuk semester ini.</p>
    </div>
    @else
    <div id="cardsContainer">
        @include('dosen.penilaian._cards')
    </div>
    @endif

    <script>
        // Toggle semester expand/collapse with color change
        function toggleSemester(id) {
            const content = document.getElementById(id);
            const btn = document.getElementById('btn-' + id);
            const icon = document.getElementById('icon-' + id);
            const badge = document.getElementById('badge-' + id);
            const badgeText = document.getElementById('badge-text-' + id);
            const title = document.getElementById('title-' + id);
            const subtitle = document.getElementById('subtitle-' + id);
            const count = document.getElementById('count-' + id);
            
            const isExpanded = content.style.maxHeight !== '0px';
            
            if (!isExpanded) {
                // Expand - change to active theme (Navy)
                content.style.maxHeight = '2000px';
                content.style.opacity = '1';
                content.style.marginTop = '0.75rem';
                icon.classList.remove('-rotate-90');
                
                // Remove default/dark classes
                btn.classList.remove('bg-white', 'dark:bg-gray-800', 'border-gray-200', 'dark:border-gray-700', 'hover:bg-gray-50', 'dark:hover:bg-gray-700');
                // Add active classes (Navy)
                btn.classList.add('bg-siakad-dark', 'border-siakad-dark', 'hover:bg-siakad-primary');
                
                // Badge
                badge.classList.remove('bg-siakad-light', 'dark:bg-gray-700');
                badge.classList.add('bg-siakad-secondary');
                
                // Text Colors
                badgeText.classList.remove('text-siakad-dark', 'dark:text-gray-200');
                badgeText.classList.add('text-white');
                
                title.classList.remove('text-siakad-dark', 'dark:text-white');
                title.classList.add('text-white');
                
                subtitle.classList.remove('text-siakad-secondary', 'dark:text-gray-400');
                subtitle.classList.add('text-gray-300');
                
                count.classList.remove('text-siakad-secondary', 'dark:text-gray-300', 'bg-siakad-light', 'dark:bg-gray-700');
                count.classList.add('text-white', 'bg-siakad-secondary');
                
                icon.classList.remove('text-siakad-secondary', 'dark:text-gray-400');
                icon.classList.add('text-white');
            } else {
                // Collapse - revert to default theme (Light/Dark auto)
                content.style.maxHeight = '0px';
                content.style.opacity = '0';
                content.style.marginTop = '0';
                icon.classList.add('-rotate-90');
                
                // Restore default/dark classes
                btn.classList.add('bg-white', 'dark:bg-gray-800', 'border-gray-200', 'dark:border-gray-700', 'hover:bg-gray-50', 'dark:hover:bg-gray-700');
                btn.classList.remove('bg-siakad-dark', 'border-siakad-dark', 'hover:bg-siakad-primary');
                
                // Badge
                badge.classList.add('bg-siakad-light', 'dark:bg-gray-700');
                badge.classList.remove('bg-siakad-secondary');
                
                // Text Colors
                badgeText.classList.add('text-siakad-dark', 'dark:text-gray-200');
                badgeText.classList.remove('text-white');
                
                title.classList.add('text-siakad-dark', 'dark:text-white');
                title.classList.remove('text-white');
                
                subtitle.classList.add('text-siakad-secondary', 'dark:text-gray-400');
                subtitle.classList.remove('text-gray-300');
                
                count.classList.add('text-siakad-secondary', 'dark:text-gray-300', 'bg-siakad-light', 'dark:bg-gray-700');
                count.classList.remove('text-white', 'bg-siakad-secondary');
                
                icon.classList.add('text-siakad-secondary', 'dark:text-gray-400');
                icon.classList.remove('text-white');
            }
        }

        // Expand all semesters
        function expandAll() {
            document.querySelectorAll('.semester-content').forEach(el => {
                el.style.maxHeight = '2000px';
                el.style.opacity = '1';
                el.style.marginTop = '0.75rem';
            });
            document.querySelectorAll('[id^="icon-semester"]').forEach(icon => {
                icon.classList.remove('-rotate-90');
            });
        }

        // Collapse all semesters
        function collapseAll() {
            document.querySelectorAll('.semester-content').forEach(el => {
                el.style.maxHeight = '0px';
                el.style.opacity = '0';
                el.style.marginTop = '0';
            });
            document.querySelectorAll('[id^="icon-semester"]').forEach(icon => {
                icon.classList.add('-rotate-90');
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const semesterFilter = document.getElementById('semesterFilter');
            const cardsContainer = document.getElementById('cardsContainer');
            let searchTimeout;

            function fetchData() {
                const search = searchInput.value;
                const semester = semesterFilter.value;
                const url = `{{ route('dosen.penilaian.index') }}?search=${encodeURIComponent(search)}&semester=${encodeURIComponent(semester)}`;

                cardsContainer.style.opacity = '0.5';

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    cardsContainer.innerHTML = html;
                    cardsContainer.style.opacity = '1';
                })
                .catch(err => {
                    console.error(err);
                    cardsContainer.style.opacity = '1';
                });
            }

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => fetchData(), 300);
            });

            semesterFilter.addEventListener('change', function() {
                fetchData();
            });
        });
    </script>

    <style>
        #cardsContainer {
            transition: opacity 0.2s ease-in-out;
        }
        .semester-content {
            transition: max-height 0.3s ease-in-out, opacity 0.2s ease-in-out, margin-top 0.2s ease-in-out;
        }
    </style>
</x-app-layout>
