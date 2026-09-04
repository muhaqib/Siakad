# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2025-12-26

### Added
- 🎓 **Mahasiswa Features**
  - KRS (Kartu Rencana Studi) online dengan validasi SKS otomatis
  - IPS-based SKS limit calculation
  - Transkrip nilai dengan perhitungan IPK/IPS
  - KHS (Kartu Hasil Studi) per semester
  - Jadwal kuliah mingguan
  - Presensi/kehadiran per mata kuliah
  - AI Academic Advisor (Gemini AI integration)
  - Manajemen Skripsi dan tracking bimbingan
  - Manajemen Kerja Praktek dan logbook
  - E-Learning (LMS) - akses materi dan tugas
  - Export PDF (transkrip, KHS)

- 👨‍🏫 **Dosen Features**
  - Input nilai mahasiswa per kelas
  - Manajemen presensi kelas
  - Approval KRS mahasiswa bimbingan
  - Review bimbingan skripsi
  - Review logbook kerja praktek
  - Upload materi kuliah
  - Kelola tugas dan penilaian
  - Absensi kehadiran dosen

- 👨‍💼 **Admin Features**
  - Dashboard dengan statistik akademik
  - Master data (Fakultas, Prodi, Mata Kuliah, Kelas)
  - User management (Dosen, Mahasiswa)
  - Monitoring KRS approval
  - Manajemen Skripsi & KP
  - Manajemen ruangan
  - Monitoring kehadiran dosen
  - Faculty-scoped admin access

- 🔐 **Security**
  - Role-based access control (RBAC)
  - Rate limiting on sensitive endpoints
  - Security headers middleware
  - Input validation with custom exceptions
  - Faculty-scoped data access

- ⚡ **Performance**
  - Database indexes on frequently queried columns
  - N+1 query prevention
  - Master data caching strategy
  - Cache warming command

- 🛠️ **Developer Experience**
  - Custom exception classes (SiakadException, KrsException)
  - CacheService for centralized caching
  - AkademikCalculationService for IPS/IPK
  - Comprehensive database seeders
  - One-command setup (`composer setup`)
  - Development mode (`composer dev`)

### Technical Stack
- Laravel 12.x
- PHP 8.2+
- TailwindCSS 3.x
- Alpine.js 3.x
- Vite 7.x
- Spatie Permission 6.x

---

## How to Update

When releasing a new version:

1. Update this CHANGELOG.md
2. Update version in `config/siakad.php`
3. Create a git tag: `git tag -a v1.0.0 -m "Release v1.0.0"`
4. Push the tag: `git push origin v1.0.0`
