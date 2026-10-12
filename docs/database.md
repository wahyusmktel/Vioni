# Database Engine & Query Builder Vioni

Vioni dilengkapi dengan Database Engine bertenaga yang ringan, tanpa beban *over-engineering* seperti ORM konvensional, serta menerapkan **100% Parameterized Prepared Statements** anti-SQL Injection.

---

## 🛡️ Prinsip Keamanan Anti-SQL Injection

1. **Native Prepared Statements Terisolasi**:  
   Fitur `PDO::ATTR_EMULATE_PREPARES` dimatikan secara default (`false`). Ini memaksa engine database (MySQL / SQLite / PostgreSQL) untuk memproses SQL dan data secara terpisah. Penyerang tidak dapat memanipulasi struktur query melalui input form.
2. **Strict Identifier Whitelisting**:  
   Nama tabel dan kolom divalidasi dengan regex ketat `^[a-zA-Z0-9_\.]+$`. Nilai aneh yang berpotensi injection akan langsung ditolak dengan `InvalidArgumentException`.
3. **Operator Whitelisting**:  
   Hanya operator SQL yang sah (`=`, `!=`, `>`, `<`, `>=`, `<=`, `LIKE`, dll) yang diizinkan dalam klausa `where`.
4. **Mass-Assignment Defense**:  
   Model dilengkapi dengan properti `$fillable` untuk mencegah eksploitasi pengisian data tak berizin (*Over-Posting Vulnerability*).

---

## ⚙️ Konfigurasi (`config/database.php`)

Vioni mendukung **SQLite** (bawaan untuk development tanpa server) dan **MySQL/MariaDB**:

```php
return [
    'default' => 'sqlite', // atau 'mysql'

    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => dirname(__DIR__) . '/database/database.sqlite',
        ],
        'mysql' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'vioni_app',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
        ],
    ],
];
```

---

## 🔍 Fluent Query Builder (`DB::table`)

Akses tabel secara langsung tanpa mendefinisikan Model:

```php
use Vioni\Database\DB;

// Mengambil seluruh data
$users = DB::table('users')->get();

// Mengambil dengan filter & pengurutan
$activeUsers = DB::table('users')
    ->select(['id', 'name', 'email'])
    ->where('status', '=', 'active')
    ->where('age', '>=', 18)
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->get();

// Mengambil 1 baris pertama
$user = DB::table('users')->where('email', 'john@vioni.dev')->first();

// Mencari berdasarkan Primary Key
$user = DB::table('users')->find(1);

// Menghitung jumlah data
$total = DB::table('users')->where('status', 'active')->count();

// Insert data baru (mengembalikan lastInsertId)
$newId = DB::table('users')->insert([
    'name' => 'Alice',
    'email' => 'alice@vioni.dev',
    'status' => 'active',
]);

// Update data (mengembalikan jumlah baris yang terpengaruh)
DB::table('users')
    ->where('id', '=', $newId)
    ->update(['status' => 'inactive']);

// Delete data
DB::table('users')->where('id', '=', $newId)->delete();
```

---

## 🏛️ Vioni Active Record Models

Model Vioni sangat bersih, ringan, dan ramah auto-completion IDE.

### 1. Membuat Model via CLI
```bash
php vioni make:model Product
```

Berkas akan digenerate di `app/Models/Product.php`:

```php
namespace App\Models;

use Vioni\Database\Model;

class Product extends Model
{
    protected static string $table = 'products';
    protected static string $primaryKey = 'id';

    // Kolom yang aman diisi secara massal
    protected static array $fillable = [
        'name',
        'price',
        'stock',
    ];
}
```

### 2. Operasi CRUD dengan Model

```php
use App\Models\Product;

// 1. Ambil semua produk
$products = Product::all();

// 2. Cari berdasarkan ID
$product = Product::find(1);
echo $product->name;

// 3. Tambah produk baru
$product = Product::create([
    'name' => 'MacBook Pro M4',
    'price' => 25000000,
    'stock' => 10,
]);

// 4. Update produk
$product->price = 24000000;
$product->save();

// 5. Hapus produk
$product->delete();
```

---

## ⚡ Transaksi Database Atomik (ACID)

Jalankan serangkaian query dalam satu transaksi aman. Jika salah satu query gagal, Vioni otomatis melakukan `rollBack()`:

```php
use Vioni\Database\DB;

DB::transaction(function () {
    DB::table('accounts')->where('id', 1)->update(['balance' => 500000]);
    DB::table('accounts')->where('id', 2)->update(['balance' => 1500000]);
});
```
