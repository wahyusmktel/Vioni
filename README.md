# Vioni PHP Framework 💜

> **Fast, Clean, Type-Safe, and Cyber-Secure Web Framework for PHP 8.4+**

Vioni adalah framework web PHP modern yang dirancang untuk mengatasi kelemahan framework generasi sebelumnya: tanpa sihir berlebih (*zero-magic*), mengedepankan keamanan siber (*cyber security by default*), struktur folder yang rapi dan konsisten, serta performa tinggi.

---

## 🌟 Fitur Utama (Milestone 1)

* **PHP 8.4 Strict & Type-Safe**: Pemanfaatan penuh tipe data modern, tanpa `__call` bayangan.
* **Cyber Security by Default**:
  * Otomatis menyematkan **OWASP Security Headers** pada setiap respons (`X-Frame-Options`, `X-Content-Type-Options`, dll).
  * Perlindungan terhadap **Null-Byte Injection** & URI poisoning.
  * Proteksi kebocoran informasi (*safe exception handler* untuk environment production).
* **Smart Routing**:
  * Parameter URL dinamis dengan auto type-casting (contoh: `/users/{id}`).
  * Dukungan Closure dan Controller method.
  * Smart Dependency Injection untuk `Request`.
* **Automatic Content Serialization**:
  * Array & Object otomatis diserialisasi menjadi JSON.
  * String disajikan langsung sebagai HTML.
* **Living Documentation**: Dokumentasi terperinci siap pakai di direktori `docs/`.

---

## 🚀 Menjalankan Server Lokal

Jalankan perintah berikut pada terminal:

```bash
php -S localhost:8000 -t public
```

Buka browser Anda di [http://localhost:8000](http://localhost:8000).

---

## 🧪 Menjalankan Automated Tests

```bash
php tests/test_run.php
```

---

## 📚 Dokumentasi Lengkap

* [Pengenalan & Filosofi](docs/index.md)
* [Arsitektur & Siklus Hidup](docs/architecture.md)
* [Panduan Routing](docs/routing.md)
* [Standar Keamanan Siber](docs/security.md)
