# Standar Keamanan Siber (Cyber Security) Vioni

Keamanan adalah pilar utama dari Vioni PHP Framework. Vioni menerapkan prinsip *Defense-in-Depth* dan *Security-by-Default* untuk melindungi aplikasi web dari berbagai celah kerentanan modern.

---

## 🛡️ 1. Automatic OWASP Security Headers

Setiap respons HTTP yang dikeluarkan oleh Vioni otomatis menyematkan header keamanan yang direkomendasikan oleh OWASP:

| Header | Nilai Default | Manfaat Keamanan |
| :--- | :--- | :--- |
| **`X-Content-Type-Options`** | `nosniff` | Mencegah browser menebak (*MIME-sniffing*) tipe file selain yang dideklarasikan. |
| **`X-Frame-Options`** | `SAMEORIGIN` | Mencegah serangan **Clickjacking** dengan melarang website lain me-render aplikasi di dalam iframe. |
| **`Referrer-Policy`** | `strict-origin-when-cross-origin` | Mencegah kebocoran data sensitif di URL saat berpindah ke domain lain. |
| **`Permissions-Policy`** | `camera=(), microphone=(), geolocation=()` | Menonaktifkan akses sensor perangkat berbahaya secara default. |
| **`X-Permitted-Cross-Domain-Policies`** | `none` | Memblokir file cross-domain Flash dan PDF policy. |

Pengaturan header ini dapat disesuaikan pada file [`config/security.php`](../config/security.php).

---

## 🚫 2. Null-Byte Injection & URI Sanitization

Serangan Null-Byte Injection (`\0` atau `%00`) dapat mengelabui fungsi file dan traversal sistem.
Di dalam `Vioni\Http\Request`:
* Seluruh URI dibersihkan dari karakter null byte sebelum masuk ke router.
* Path dinormalisasi untuk mencegah trik leading/trailing slash poisoning.

---

## 🔒 3. Safe Exception Handling (Anti Information Disclosure)

Pada banyak framework yang salah konfigurasi, error 500 mengekspos kata sandi database, API keys, dan jejak direktori server (*full path disclosure*).

Di Vioni:
* Jika `config('debug') === true` (Mode Development): Detail stack trace ditampilkan rapi dalam format JSON untuk kemudahan debugging developer.
* Jika `config('debug') === false` (Mode Production): Detail internal **sepenuhnya disembunyikan**, hanya menghasilkan respons generik:
  ```json
  {
      "error": "Internal Server Error",
      "message": "An unexpected error occurred. Please contact the administrator."
  }
  ```

---

## 🧪 4. Type-Safe Input & Casting

Mencegah serangan *type juggling* PHP dengan menyediakan fungsi input bertipe kuat:
* `$request->int('user_id')`: Menjamin nilai yang didapat adalah integer valid.
* `$request->str('keyword')`: Menjamin nilai yang didapat adalah string.
* `$request->bool('is_active')`: Validasi boolean aman.
