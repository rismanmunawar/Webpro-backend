# WebPro Backend API (Laravel 12 + Laravel Sanctum)

Backend RESTful API untuk WebPro App, dibangun dengan Laravel 12 dan diamankan menggunakan **Laravel Sanctum** (Bearer Token Authentication).

---

## 🚀 Fitur Utama

- **Laravel Sanctum Authentication**:
  - `POST /api/register` – Registrasi user baru & otomatis issue Bearer token.
  - `POST /api/login` – Login dengan email & password, menghasilkan Sanctum Bearer token.
  - `GET /api/me` atau `GET /api/user` – Ambil data profil user yang sedang login.
  - `PUT /api/profile` – Update profil atau password user yang terautentikasi.
  - `POST /api/logout` – Revoke token perangkat saat ini.
  - `POST /api/logout-all` – Revoke seluruh token dari semua perangkat.
- **User Management (CRUD)**:
  - `GET /api/users` – Daftar user dengan pagination, filter `status`, `role`, `department`, dan pencarian teks.
  - `POST /api/users` – Tambah anggota user baru.
  - `GET /api/users/{id}` – Detail user.
  - `PUT /api/users/{id}` – Update data user.
  - `DELETE /api/users/{id}` – Hapus user.
- **CORS Configured**:
  - Siap diakses oleh client frontend (misal Vite di `http://localhost:5173` atau production domain).
- **Automated Feature Tests**:
  - Test suite lengkap untuk Auth & CRUD User (11 tests, 105 assertions).

---

## 🔑 Demo Credentials (Database Seeder)

Database sudah disiapkan dengan akun default:

| Role | Email | Password |
|---|---|---|
| **Administrator** | `admin@webpro.com` | `password123` |
| **Editor** | `emma@company.com` | `password123` |
| **Admin Team** | `james@company.com` | `password123` |
| **Moderator** | `sofia@company.com` | `password123` |

---

## 🛠️ Cara Menjalankan Backend

### 1. Jalankan MySQL di Laragon
Pastikan MySQL service di Laragon dalam kondisi aktif (**Start All**). Database default: `webpro_app`.

### 2. Jalankan Server Artisan
Buka terminal di folder `backend/`:
```bash
php artisan serve
```
Server akan berjalan di `http://127.0.0.1:8000`.

### 3. Migrasi & Seeder Ulang (Opsional jika ingin reset data)
```bash
php artisan migrate:fresh --seed
```

### 4. Menjalankan Automated Test
```bash
php artisan test
```

---

## 📖 Dokumentasi Endpoint API

### 1. Public Endpoints

#### `POST /api/login`
**Request Body:**
```json
{
  "email": "admin@webpro.com",
  "password": "password123",
  "device_name": "Web Browser"
}
```
**Response (200 OK):**
```json
{
  "status": "success",
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Aigars Silkalns",
      "email": "admin@webpro.com",
      "role": "Admin",
      "department": "Engineering"
    },
    "token": "1|abcdef123456789...",
    "token_type": "Bearer"
  }
}
```

#### `POST /api/register`
**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "Editor",
  "department": "Marketing"
}
```

---

### 2. Protected Endpoints (Header: `Authorization: Bearer <token>`)

#### `GET /api/me`
Mengambil detail profil akun yang sedang login.

#### `PUT /api/profile`
Memperbarui profil akun yang sedang login.

#### `POST /api/logout`
Membatalkan (revoke) Bearer token aktif perangkat saat ini.

#### `GET /api/users`
Mengambil list user untuk halaman manajemen pengguna.
**Query Parameters:**
- `page`: nomor halaman (default: `1`)
- `per_page`: jumlah item per halaman (default: `10`)
- `search`: kata kunci pencarian (nama, email, departemen, role)
- `status`: `All`, `Active`, `Inactive`, `Suspended`
- `role`: `Admin`, `Editor`, `Moderator`, `Viewer`
- `sort_by`: `id`, `name`, `email`, `role`, `department`, `status`, `created_at`
- `sort_order`: `asc` atau `desc`
