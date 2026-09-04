<h1 align="center"> SIAKAD AI - Sistem Informasi Akademik</h1>

<p align="center">
  <strong>Modern Academic Information System built with Laravel 12</strong>
</p>

<p align="center">
  <a href="#features">Features</a> •
  <a href="#tech-stack">Tech Stack</a> •
  <a href="#installation">Installation</a> •
  <a href="#configuration">Configuration</a> •
  <a href="#usage">Usage</a> •
  <a href="#api-documentation">API</a> •
  <a href="#contributing">Contributing</a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-red?style=flat-square&logo=laravel" alt="Laravel Version">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue?style=flat-square&logo=php" alt="PHP Version">
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-38bdf8?style=flat-square&logo=tailwindcss" alt="TailwindCSS">
  <img src="https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=flat-square&logo=alpine.js" alt="Alpine.js">
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square" alt="License">
</p>

---

## Overview

**SIAKAD** (Sistem Informasi Akademik) adalah aplikasi manajemen akademik modern yang dirancang untuk universitas dan perguruan tinggi. Dibangun dengan Laravel 12 dan menggunakan arsitektur yang clean, scalable, dan production-ready.

### Highlights

- **Modern UI** - Responsive design dengan TailwindCSS dan Alpine.js
- **AI-Powered** - Academic Advisor berbasis Gemini AI
- **Secure** - Role-based access control, rate limiting, dan security headers
- **Fast** - Optimized queries, caching strategy, dan database indexes
- **Responsive** - Mobile-friendly interface
- **Indonesian Locale** - Fully localized untuk bahasa Indonesia

---

## Features

### Mahasiswa
| Feature | Description |
|---------|-------------|
| **KRS Online** | Pengisian Kartu Rencana Studi dengan validasi SKS otomatis |
| **Transkrip Nilai** | Lihat transkrip akademik lengkap dengan IPK/IPS |
| **KHS** | Kartu Hasil Studi per semester |
| **Presensi** | Riwayat kehadiran per mata kuliah |
| **Jadwal Kuliah** | Jadwal perkuliahan mingguan |
| **E-Learning (LMS)** | Akses materi dan tugas kuliah |
| **AI Academic Advisor** | Konsultasi akademik dengan AI Gemini |
| **Skripsi** | Tracking progress skripsi dan bimbingan |
| **Kerja Praktek** | Manajemen KP dan logbook |
| **Export PDF** | Download transkrip dan KHS dalam format PDF |

### Dosen
| Feature | Description |
|---------|-------------|
| **Input Nilai** | Input nilai mahasiswa per kelas |
| **Presensi Kelas** | Kelola pertemuan dan presensi mahasiswa |
| **Bimbingan PA** | Persetujuan KRS mahasiswa bimbingan |
| **Bimbingan Skripsi** | Review bimbingan dan update status skripsi |
| **Bimbingan KP** | Review logbook kerja praktek |
| **LMS Management** | Upload materi dan kelola tugas |
| **Kehadiran** | Absensi kehadiran dosen |

### Admin
| Feature | Description |
|---------|-------------|
| **Dashboard** | Statistik dan overview akademik |
| **Master Data** | Kelola Fakultas, Prodi, Mata Kuliah, Kelas |
| **User Management** | Kelola akun Dosen dan Mahasiswa |
| **KRS Approval** | Monitoring dan approval KRS (view only) |
| **Skripsi & KP** | Assign pembimbing dan update status |
| **Ruangan** | Manajemen ruang kuliah |
| **Kehadiran Dosen** | Monitoring kehadiran dosen |

### Security Features
- ✅ Role-based access control (RBAC)
- ✅ Faculty-scoped admin access
- ✅ Rate limiting pada endpoint sensitif
- ✅ CSRF protection
- ✅ Security headers middleware
- ✅ Input validation & sanitization

---

## Tech Stack

### Backend
| Technology | Version | Description |
|------------|---------|-------------|
| **PHP** | 8.2 | Server-side language |
| **Laravel** | 12 | PHP Framework |
| **Laravel Breeze** | 2 | Authentication scaffolding |
| **Spatie Permission** | 6 | Role & permission management |

### Frontend
| Technology | Version | Description |
|------------|---------|-------------|
| **TailwindCSS** | 3 | Utility-first CSS framework |
| **Alpine.js** | 3 | Lightweight JavaScript framework |
| **Vite** | 7 | Frontend build tool |

### Database
| Technology | Description |
|------------|-------------|
| **MySQL** | Recommended for production |
| **PostgreSQL** | Alternative production database |
| **SQLite** | Development & testing |

### AI Integration
| Technology | Description |
|------------|-------------|
| **Google Gemini API** | AI Academic Advisor |

---

## Installation

### Prerequisites

- PHP 8.2 or higher
- Composer 2..x
- Node.js 18+ & npm
- MySQL 8.0+ / PostgreSQL 14+ (for production)

### Quick Start

```bash
# 1. Clone the repository
git clone https://github.com/ryandaaa/siakad.git
cd siakad

# 2. Install PHP dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Install Node.js dependencies
npm install

# 6. Build frontend assets
npm run build

# 7. Run database migrations with seeders
php artisan migrate --seed

# 8. Start the development server
php artisan serve
```

### One-Command Setup

```bash
composer setup
```

This will automatically:
- Install Composer dependencies
- Copy `.env.example` to `.env`
- Generate application key
- Run migrations
- Install npm dependencies
- Build frontend assets

### Development Mode

```bash
composer dev
```

This starts all development services concurrently:
- Laravel development server (`php artisan serve`)
- Queue worker (`php artisan queue:listen`)
- Log viewer (`php artisan pail`)
- Vite dev server (`npm run dev`)

---

## ⚙️ Configuration

### Environment Variables

#### Database (MySQL - Production)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siakad
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### Database (SQLite - Development)
```env
DB_CONNECTION=sqlite
```

#### AI Integration (Gemini)
```env
# Get your API key at: https://aistudio.google.com/
GEMINI_API_KEY=your_gemini_api_key
```

#### Cache & Session (Production)
```env
SESSION_DRIVER=database
CACHE_STORE=database

# Or with Redis (recommended):
SESSION_DRIVER=redis
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Academic Configuration

Edit `config/siakad.php` to customize:

```php
return [
    // SKS limits based on IPS
    'maks_sks' => [
        'default' => 24,
        'ips_rules' => [
            ['min' => 3.51, 'max' => 4.00, 'sks' => 24],
            ['min' => 3.01, 'max' => 3.50, 'sks' => 22],
            ['min' => 2.51, 'max' => 3.00, 'sks' => 20],
            ['min' => 2.00, 'max' => 2.50, 'sks' => 18],
            ['min' => 0.00, 'max' => 1.99, 'sks' => 14],
        ]
    ],

    // Grade conversion
    'nilai_konversi' => [
        ['min' => 85, 'max' => 100, 'huruf' => 'A',  'bobot' => 4.00],
        ['min' => 80, 'max' => 84,  'huruf' => 'A-', 'bobot' => 3.75],
        ['min' => 75, 'max' => 79,  'huruf' => 'B+', 'bobot' => 3.50],
        // ... more grades
    ],

    // Default class capacity
    'kelas_kapasitas_default' => 40,
    
    // Pagination
    'pagination' => 15,
];
```

---

## Default Users

After running seeders, you can login with:

| Role | Email | Password | Description |
|------|-------|----------|-------------|
| **Superadmin** | `superadmin@siakad.test` | `password` | Full system access |
| **Admin Fakultas** | `admin.ftik@siakad.test` | `password` | Faculty-scoped admin |
| **Dosen** | `dosen@siakad.test` | `password` | Dr. Ahmad Fauzi, M.Kom. |
| **Mahasiswa** | `mahasiswa@siakad.test` | `password` | Budi Santoso (Semester 5, NIM: 2022101001) |

> **Important**: Change these passwords immediately in production!

---

## Project Structure

```
siakad/
├── app/
│   ├── Console/Commands/      # Artisan commands
│   ├── DTOs/                  # Data Transfer Objects
│   ├── Exceptions/            # Custom exceptions
│   ├── Helpers/               # Helper classes
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/         # Admin controllers
│   │   │   ├── Dosen/         # Dosen controllers
│   │   │   └── Mahasiswa/     # Mahasiswa controllers
│   │   └── Middleware/        # Custom middleware
│   ├── Models/                # Eloquent models
│   └── Services/              # Business logic services
├── config/
│   └── siakad.php             # Academic configuration
├── database/
│   ├── factories/             # Model factories
│   ├── migrations/            # Database migrations
│   └── seeders/               # Database seeders
├── resources/
│   └── views/
│       ├── admin/             # Admin views
│       ├── dosen/             # Dosen views
│       ├── mahasiswa/         # Mahasiswa views
│       ├── components/        # Blade components
│       └── layouts/           # Layout templates
└── routes/
    └── web.php                # Web routes
```

---

## Database Schema

### Core Tables

```
┌──────────────┐     ┌─────────────┐     ┌────────────┐
│    users     │     │   fakultas  │     │   prodi    │
├──────────────┤     ├─────────────┤     ├────────────┤
│ id           │     │ id          │     │ id         │
│ name         │     │ nama        │     │ nama       │
│ email        │     │ kode        │     │ kode       │
│ role         │     └─────────────┘     │ fakultas_id│
│ fakultas_id  │                         └────────────┘
└──────────────┘                                │
       │                                        │
       ▼                                        ▼
┌──────────────┐                         ┌────────────┐
│  mahasiswa   │                         │   dosen    │
├──────────────┤                         ├────────────┤
│ id           │                         │ id         │
│ user_id      │                         │ user_id    │
│ nim          │                         │ nidn       │
│ prodi_id     │                         │ prodi_id   │
│ angkatan     │                         └────────────┘
│ dosen_pa_id  │                                │
└──────────────┘                                │
       │                                        ▼
       │         ┌────────────┐          ┌────────────┐
       │         │ mata_kuliah│          │   kelas    │
       │         ├────────────┤          ├────────────┤
       │         │ id         │◄─────────│ mata_kuliah│
       │         │ kode_mk    │          │ dosen_id   │
       │         │ nama_mk    │          │ nama_kelas │
       │         │ sks        │          │ kapasitas  │
       │         │ semester   │          └────────────┘
       │         └────────────┘                 │
       │                                        │
       ▼                                        ▼
┌──────────────┐                         ┌────────────┐
│     krs      │                         │ krs_detail │
├──────────────┤                         ├────────────┤
│ id           │◄────────────────────────│ krs_id     │
│ mahasiswa_id │                         │ kelas_id   │
│ tahun_akad_id│                         └────────────┘
│ status       │
└──────────────┘
```

### Additional Tables
- `nilai` - Student grades
- `jadwal_kuliah` - Class schedules
- `pertemuan` - Class meetings
- `presensi` - Attendance records
- `skripsi` - Thesis management
- `bimbingan_skripsi` - Thesis guidance
- `kerja_praktek` - Internship
- `logbook_kp` - Internship logbook
- `materi` - Learning materials
- `tugas` - Assignments
- `tugas_submission` - Assignment submissions
- `notifications` - System notifications
- `ai_conversation_logs` - AI chat logs

---

## Artisan Commands

```bash
# Cache warming (after deployment)
php artisan cache:warm

# Clear all caches
php artisan cache:warm --clear

# Run migrations
php artisan migrate

# Seed database with sample data
php artisan db:seed

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

---

## Testing

```bash
# Run all tests
php artisan test

# Or using Pest directly
./vendor/bin/pest

# Run with coverage
php artisan test --coverage
```

---

## Security

### Rate Limiting
- AI Chat: 10 requests/minute per user
- KRS Operations: 10 requests/minute per user
- Penilaian: 20 requests/minute per user

### Middleware
- `role` - Role-based access control
- `fakultas.scope` - Faculty-scoped data access
- `SecurityHeadersMiddleware` - Security headers

### Validation
- All inputs are validated using Laravel Form Requests
- Custom exceptions for business logic errors
- CSRF protection on all forms

---

## Performance

### Optimizations
- Database indexes on frequently queried columns
- Query optimization (N+1 prevention)
- Master data caching (1 hour TTL)
- Eager loading relationships

### Caching Strategy
```php
// Master data cached:
- Tahun Akademik Aktif
- Fakultas list
- Prodi list
- Mata Kuliah list
- Dosen list
```

---

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation as needed
- Keep commits atomic and well-described

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## Author

Developed with ❤️ by Ryanda

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com/) - The PHP Framework for Web Artisans
- [TailwindCSS](https://tailwindcss.com/) - A utility-first CSS framework
- [Alpine.js](https://alpinejs.dev/) - A rugged, minimal JavaScript framework
- [Google Gemini](https://deepmind.google/technologies/gemini/) - AI for Academic Advisor

---

<p align="center">
  Made with ☕ and Laravel
</p>
