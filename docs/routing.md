# Panduan Routing Vioni

Routing di Vioni didesain sangat intuitif, eksplisit, dan ramah auto-complete IDE.

---

## 📌 Registrasi Rute Dasar

Rute didefinisikan menggunakan class `Vioni\Routing\Route` di dalam berkas `routes/web.php` atau `routes/api.php`.

```php
use Vioni\Routing\Route;

// Rute GET sederhana mengembalikan string HTML
Route::get('/', function () {
    return '<h1>Selamat Datang di Vioni</h1>';
});

// Rute POST menerima data
Route::post('/submit', function () {
    return ['status' => 'submitted'];
});
```

Method HTTP yang didukung:
* `Route::get(string $uri, $action)`
* `Route::post(string $uri, $action)`
* `Route::put(string $uri, $action)`
* `Route::delete(string $uri, $action)`
* `Route::patch(string $uri, $action)`

---

## 🎯 Parameter URL Dinamis & Auto Type-Casting

Gunakan tanda kurung kurawal `{param}` untuk menangkap nilai dinamis dari URL:

```php
Route::get('/users/{id}', function (int $id) {
    // $id otomatis dicasting ke tipe data integer
    return [
        'user_id' => $id,
        'type' => gettype($id), // 'integer'
    ];
});
```

Bisa juga menggunakan beberapa parameter sekaligus:

```php
Route::get('/posts/{slug}/comments/{commentId}', function (string $slug, int $commentId) {
    return [
        'post' => $slug,
        'comment' => $commentId,
    ];
});
```

---

## 🕹️ Menggunakan Controller

Alih-alih menggunakan Closure, Anda bisa mengarahkan rute ke method Controller:

```php
use App\Controllers\UserController;
use Vioni\Routing\Route;

Route::get('/profile/{id}', [UserController::class, 'show']);
```

Contoh implementasi Controller:

```php
namespace App\Controllers;

use Vioni\Http\Request;

class UserController
{
    public function show(Request $request, int $id): array
    {
        return [
            'id' => $id,
            'ip' => $request->ip(),
            'query' => $request->all(),
        ];
    }
}
```

> **Smart Dependency Injection:**
> Vioni otomatis mendeteksi apakah method Controller Anda membutuhkan instance `Vioni\Http\Request` berdasarkan type-hint. Urutan parameter tidak menjadi masalah!
