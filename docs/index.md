# Vioni PHP Framework

> **Fast, Clean, Type-Safe, and Cyber-Secure Web Framework for PHP 8.4+**

Selamat datang di dokumentasi resmi **Vioni PHP Framework**. Vioni lahir dari dedikasi untuk menghadirkan pengalaman pengembangan web (*Developer Experience*) yang bersih, cepat, tanpa trik sulap (*zero-magic*), serta mengutamakan standar **keamanan siber (cyber security)** sejak baris pertama kode.

---

## 🌟 Filosofi Desain: "The Vioni Way"

1. **Zero-Magic & 100% Type-Safe**
   Tidak ada Facades misterius atau method bayangan (`__call`, `__callStatic`) yang membingungkan IDE. Semua kode Vioni murni strongly-typed PHP 8.4 dengan auto-completion penuh tanpa ekstensi khusus.

2. **Cyber Security by Default**
   Keamanan bukan pilihan tambahan, melainkan fitur bawaan:
   * **OWASP Defense Headers** otomatis disertakan pada setiap HTTP response.
   * **Null-byte Injection Prevention** dan sanitasi URI otomatis.
   * **Information Disclosure Protection**: Error stack trace tidak akan pernah bocor di environment production.

3. **Automatic Content Serialization**
   Return nilai apa pun dari Route atau Controller kamu tanpa boilerplate:
   * Return `array` atau `object` ➔ Otomatis diserialisasi menjadi `application/json`.
   * Return `string` ➔ Otomatis disajikan sebagai `text/html`.

4. **Struktur Folder Teratur & Intuitif**
   Pemisahan tegas antara **Engine Layer** (`core/`) dan **Application Layer** (`app/`), menjamin keterbacaan kode baik bagi pemula maupun arsitek enterprise.

---

## 🚀 Persyaratan Sistem

* **PHP:** Versi 8.4.0 atau lebih baru.
* **Composer:** Versi 2.x.
* Ekstensi PHP yang disarankan: `mbstring`, `json`, `filter`.

---

## 🧭 Daftar Dokumentasi

* [Arsitektur & Siklus Hidup](architecture.md)
* [Panduan CLI (`php vioni`)](cli.md)
* [Panduan Routing](routing.md)
* [Panduan Middleware](middleware.md)
* [Standar Keamanan Siber](security.md)
