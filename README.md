# Vioni PHP Framework 💜

> **Fast, Clean, Type-Safe, and Cyber-Secure Web Framework for PHP 8.4+**

Vioni adalah framework web PHP modern yang dirancang untuk mengatasi kelemahan framework generasi sebelumnya: tanpa sihir berlebih (*zero-magic*), mengedepankan keamanan siber (*cyber security by default*), struktur folder yang rapi dan konsisten, serta performa tinggi.

---

## 🌟 Fitur Unggulan

* **PHP 8.4 Strict & Type-Safe**: Pemanfaatan penuh tipe data modern, tanpa `__call` bayangan.
* **Vioni CLI Runner (`php vioni`)**:
  * `php vioni serve`: Menjalankan server dev lokal.
  * `php vioni route:list`: Menampilkan tabel seluruh rute.
  * `php vioni key:generate`: Menghasilkan kunci enkripsi AES-256 (256-bit).
  * `php vioni security:audit`: Audit otomatis kesiapan keamanan aplikasi.
  * `php vioni make:controller` & `make:middleware`: Scaffolding kode instan.
  * `php vioni test`: Menjalankan test suite bawaan.
* **Cyber Security by Default**:
  * Otomatis menyematkan **OWASP Security Headers** pada setiap respons (`X-Frame-Options`, `X-Content-Type-Options`, dll).
  * **Proteksi CSRF** bawaan dengan verifikasi *timing-attack-safe* (`hash_equals`).
  * Perlindungan terhadap **Null-Byte Injection** & URI poisoning.
  * Proteksi kebocoran informasi (*safe exception handler* untuk environment production).
* **Onion Middleware Pipeline**:
  * Dukungan middleware global maupun per-rute.
* **Smart Routing**:
  * Parameter URL dinamis dengan auto type-casting (contoh: `/users/{id}`).
  * Dukungan Closure dan Controller method.
  * Smart Dependency Injection untuk `Request`.
* **Automatic Content Serialization**:
  * Array & Object otomatis diserialisasi menjadi JSON.
  * String disajikan langsung sebagai HTML.
* **Living Documentation**: Dokumentasi komprehensif di direktori `docs/`.

---

## 🚀 Menjalankan Server Lokal

Gunakan Vioni CLI:

```bash
php vioni serve
```

Buka browser Anda di [http://localhost:8000](http://localhost:8000).

---

## 🧪 Menjalankan Automated Tests

```bash
php vioni test
```

---

## 📚 Dokumentasi Lengkap

* [Pengenalan & Filosofi](docs/index.md)
* [Arsitektur & Siklus Hidup](docs/architecture.md)
* [Panduan CLI (`php vioni`)](docs/cli.md)
* [Panduan Routing](docs/routing.md)
* [Panduan Middleware](docs/middleware.md)
* [Standar Keamanan Siber](docs/security.md)
