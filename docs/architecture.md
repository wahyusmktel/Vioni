# Arsitektur & Siklus Hidup Aplikasi

Dokumentasi ini menjelaskan hierarki direktori dan alur eksekusi request-response di dalam **Vioni Framework**.

---

## 📂 Struktur Direktori

```text
framework/
├── app/                      # [APPLICATION LAYER] Tempat logika aplikasi pengguna
│   ├── Controllers/          # Controller penangan HTTP
│   ├── Middleware/           # Middleware filter request
│   └── Models/               # Data Model & Entitas
├── bootstrap/                # Inisialisasi framework
│   └── app.php               # Bootstrapping instance Vioni Application
├── config/                   # Berkas konfigurasi aplikasi & security
│   ├── app.php               # Pengaturan global (timezone, debug mode, env)
│   └── security.php          # Pengaturan security headers & proteksi cyber
├── core/                     # [ENGINE LAYER] Jantung Vioni Framework
│   ├── Foundation/           # Application Kernel & Exception Handler
│   ├── Http/                 # Request, Response, Cookies
│   ├── Routing/              # Route, Router, Dispatcher
│   └── Security/             # Security Headers, Sanitizer, Defense Engine
├── docs/                     # Dokumentasi resmi panduan pengguna
├── public/                   # Satu-satunya direktori yang terekspos ke internet
│   └── index.php             # Front Controller (Entry Point)
├── routes/                   # Definisi rute
│   ├── api.php               # Rute stateless REST API
│   └── web.php               # Rute web presentation / view
└── tests/                    # Pengujian otomatis (Unit & Feature Tests)
```

---

## 🔄 Siklus Hidup Request (Request Lifecycle)

Setiap request HTTP yang masuk akan melewati tahapan berikut:

1. **Entry Point (`public/index.php`)**
   Web server (Nginx/Apache/PHP built-in server) mengarahkan seluruh request ke `public/index.php`.
   
2. **Autoloading & Bootstrapping**
   Composer PSR-4 autoloader dimuat, lalu instance `Vioni\Foundation\Application` diinisialisasi melalui `bootstrap/app.php`. Seluruh rute dari `routes/web.php` dan `routes/api.php` didaftarkan.

3. **Request Capture (`Request::capture()`)**
   Input dari `$_GET`, `$_POST`, `$_SERVER`, serta body `php://input` ditangkap dan disanitasi dari potensi *null-byte injection* atau *malformed URI*.

4. **Route Dispatching (`Router::dispatch()`)**
   Router mencocokkan method HTTP dan path URI dengan daftar rute yang terdaftar.
   * Jika cocok: Parameter dinamis (seperti `{id}`) diekstrak dan dicasting sesuai tipe data.
   * Jika method salah: Menghasilkan respon `405 Method Not Allowed`.
   * Jika tidak ada rute yang cocok: Menghasilkan respon `404 Not Found`.

5. **Action Execution & Dependency Injection**
   Closure atau Controller method dieksekusi. Parameter seperti instance `Request` dan variabel route langsung di-*inject* secara otomatis berdasarkan type-hint.

6. **Automatic Response Wrapping**
   Hasil return dari action dikonversi menjadi instance `Vioni\Http\Response`:
   * `array` / `object` ➔ `application/json`
   * `string` ➔ `text/html`

7. **Security Headers Emission & Send**
   Header keamanan bawaan OWASP dipasang ke response, kemudian status code, header, dan body dikirimkan kembali ke browser atau client.
