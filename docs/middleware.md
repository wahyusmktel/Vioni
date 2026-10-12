# Panduan Middleware Vioni

Middleware menyediakan mekanisme penyaringan (*filtering*) yang fleksibel untuk request HTTP yang masuk ke aplikasi Anda sebelum mencapai Controller atau Handler utama.

---

## 🧅 Arsitektur Middleware (Onion Model)

Vioni menggunakan arsitektur pipa lapis (*Onion Pipeline*):
```
Request ──> [Middleware 1] ──> [Middleware 2] ──> [Controller Action]
Response <── [Middleware 1] <── [Middleware 2] <── [Controller Action]
```

Setiap middleware memiliki kemampuan untuk:
1. Memeriksa atau memanipulasi request sebelum diteruskan.
2. Menghentikan request lebih awal (misalnya mengembalikan respons `401 Unauthorized` atau `419 CSRF Mismatch`).
3. Memanipulasi response setelah controller selesai dipanggil.

---

## 🛠️ Membuat Middleware

Gunakan generator CLI Vioni:
```bash
php vioni make:middleware EnsureAuth
```

Berkas akan digenerate di `app/Middleware/EnsureAuth.php`:

```php
namespace App\Middleware;

use Closure;
use Vioni\Http\MiddlewareInterface;
use Vioni\Http\Request;
use Vioni\Http\Response;

class EnsureAuth implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Logika Pre-processing (Cek Autentikasi)
        if (!$request->header('Authorization')) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        // 2. Teruskan ke handler berikutnya
        $response = $next($request);

        // 3. Logika Post-processing (jika diperlukan)
        return $response;
    }
}
```

---

## 🎯 Menerapkan Middleware pada Route

Anda dapat menempelkan middleware secara langsung pada rute tertentu menggunakan method chained `->middleware(...)`:

```php
use App\Middleware\EnsureAuth;
use App\Middleware\VerifyCsrfToken;
use Vioni\Routing\Route;

// Middleware tunggal
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(EnsureAuth::class);

// Beberapa middleware sekaligus (array)
Route::post('/profile/update', [ProfileController::class, 'update'])
    ->middleware([EnsureAuth::class, VerifyCsrfToken::class]);
```

---

## 🌐 Menerapkan Middleware Global

Jika Anda ingin suatu middleware berjalan di **semua request** (misalnya logging atau session handler), daftarkan pada `Router` di `bootstrap/app.php`:

```php
use App\Middleware\VerifyCsrfToken;
use Vioni\Routing\Router;

Router::getInstance()->middleware(VerifyCsrfToken::class);
```
