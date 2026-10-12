# Panduan Vioni CLI (`php vioni`)

Vioni hadir dengan perkakas antarmuka baris perintah (*Command Line Interface*) bawaan yang bertenaga, ringkas, dan mengedepankan keamanan.

```bash
php vioni <command> [arguments] [options]
```

---

## 📋 Daftar Perintah Tersedia

### 1. General Commands

#### `serve`
Menjalankan local development server dengan document root otomatis mengarah ke folder `public/`:
```bash
php vioni serve
php vioni serve --port=8080 --host=0.0.0.0
```

#### `test`
Menjalankan automated test runner native Vioni untuk memvalidasi seluruh fungsionalitas aplikasi dan security:
```bash
php vioni test
```

---

### 2. Route Commands

#### `route:list`
Menampilkan tabel seluruh rute yang terdaftar dalam aplikasi beserta method HTTP, path URI, dan handler (Closure atau Controller):
```bash
php vioni route:list
```

---

### 3. Key Management

#### `key:generate`
Menghasilkan kunci kriptografis 256-bit (AES-256) acak berkekuatan tinggi menggunakan `random_bytes(32)` dan menyimpannya secara otomatis ke dalam file `.env`:
```bash
php vioni key:generate
```

---

### 4. Cyber Security

#### `security:audit`
🛡️ **Fitur Unggulan Khas Vioni:**  
Melakukan audit otomatis terhadap konfigurasi aplikasi dan kepatuhan standar keamanan siber:
* Status versi runtime PHP.
* Pengecekan kebocoran *Debug Mode* di environment produksi.
* Verifikasi header *Clickjacking* (`X-Frame-Options`).
* Verifikasi proteksi *MIME-sniffing* (`X-Content-Type-Options`).
* Pengecekan *Content Security Policy* (CSP).
* Pengecekan flag *Session Cookie HttpOnly*.
* Validasi keberadaan cryptographic *APP_KEY*.

```bash
php vioni security:audit
```

---

### 5. Code Generators (Scaffolding)

#### `make:controller`
Membuat class controller baru dengan standar PHP 8.4 murni:
```bash
# Controller standar
php vioni make:controller ArticleController

# API Controller dengan template CRUD response JSON
php vioni make:controller Api/ProductController --api
```

#### `make:middleware`
Membuat class middleware baru yang mengimplementasikan `Vioni\Http\MiddlewareInterface`:
```bash
php vioni make:middleware EnsureAdmin
```
