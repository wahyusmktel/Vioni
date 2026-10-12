<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Middleware\VerifyCsrfToken;
use App\Models\User;
use Vioni\Database\Connection;
use Vioni\Database\DB;
use Vioni\Foundation\Application;
use Vioni\Http\MiddlewareInterface;
use Vioni\Http\Request;
use Vioni\Http\Response;
use Vioni\Routing\Route;
use Vioni\Security\Csrf;

// Bootstrap application
/** @var Application $app */
$app = require dirname(__DIR__) . '/bootstrap/app.php';

// Setup test routes with middleware
class DummyAuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next): Response
    {
        if ($request->header('Authorization') !== 'Bearer secret-token') {
            return Response::json(['error' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}

Route::get('/protected', function () {
    return ['secret' => 'access granted'];
})->middleware(DummyAuthMiddleware::class);

Route::post('/web/form', function () {
    return ['status' => 'form accepted'];
})->middleware(VerifyCsrfToken::class);

$validToken = Csrf::token();

$testCases = [
    [
        'name' => 'Home Page HTML',
        'request' => new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/'], [], []),
        'expectedStatus' => 200,
        'contains' => 'Vioni Framework',
    ],
    [
        'name' => 'Route with parameter (/hello/TJ)',
        'request' => new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/hello/TJ'], [], []),
        'expectedStatus' => 200,
        'contains' => 'Halo TJ!',
    ],
    [
        'name' => 'API Health JSON check',
        'request' => new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/health'], [], []),
        'expectedStatus' => 200,
        'contains' => '"status":"healthy"',
    ],
    [
        'name' => 'API Controller with Typed Param & Request DI (/api/users/42)',
        'request' => new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/users/42'], [], []),
        'expectedStatus' => 200,
        'contains' => '"id":42',
    ],
    [
        'name' => 'Route 404 Not Found',
        'request' => new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/not-exists'], [], []),
        'expectedStatus' => 404,
        'contains' => 'Not Found',
    ],
    [
        'name' => 'Route 405 Method Not Allowed',
        'request' => new Request([], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/'], [], []),
        'expectedStatus' => 405,
        'contains' => 'Method Not Allowed',
    ],
    [
        'name' => 'Middleware Auth Block (401 Unauthorized)',
        'request' => new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/protected'], [], []),
        'expectedStatus' => 401,
        'contains' => 'Unauthorized',
    ],
    [
        'name' => 'Middleware Auth Pass (200 OK with Bearer Token)',
        'request' => new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/protected', 'HTTP_AUTHORIZATION' => 'Bearer secret-token'], [], []),
        'expectedStatus' => 200,
        'contains' => 'access granted',
    ],
    [
        'name' => 'CSRF Defense: Block missing token on POST /web/form (419)',
        'request' => new Request([], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/web/form'], [], []),
        'expectedStatus' => 419,
        'contains' => 'CSRF Token Mismatch',
    ],
    [
        'name' => 'CSRF Defense: Allow valid token via POST _csrf parameter (200)',
        'request' => new Request([], ['_csrf' => $validToken], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/web/form'], [], []),
        'expectedStatus' => 200,
        'contains' => 'form accepted',
    ],
    [
        'name' => 'CSRF Defense: Allow valid token via X-CSRF-TOKEN header (200)',
        'request' => new Request([], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/web/form', 'HTTP_X_CSRF_TOKEN' => $validToken], [], []),
        'expectedStatus' => 200,
        'contains' => 'form accepted',
    ],
    [
        'name' => 'CSRF Defense: Automatically bypass for excluded /api/* endpoints (200)',
        'request' => new Request([], ['message' => 'hello'], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/api/echo'], [], []),
        'expectedStatus' => 200,
        'contains' => 'Data received safely',
    ],
];

echo "=== VIONI FRAMEWORK TEST SUITE ===\n\n";

$passed = 0;
$total = count($testCases);

// 1. Run HTTP and Middleware Tests
foreach ($testCases as $case) {
    $response = $app->handle($case['request']);
    $statusMatches = $response->getStatusCode() === $case['expectedStatus'];
    $contentMatches = str_contains($response->getContent(), $case['contains']);

    if ($statusMatches && $contentMatches) {
        echo "✅ PASS: {$case['name']} (HTTP {$response->getStatusCode()})\n";
        $passed++;
    } else {
        echo "❌ FAIL: {$case['name']} (Expected: {$case['expectedStatus']}, Got: {$response->getStatusCode()})\n";
        echo "   Body: {$response->getContent()}\n";
    }
}

// 2. Database Engine & Anti-SQL Injection Tests
echo "\n--- DATABASE ENGINE & MODEL TESTS ---\n";

// In-Memory SQLite Setup
$testPdo = Connection::createConnection(['driver' => 'sqlite', 'database' => ':memory:']);
Connection::setPdo($testPdo);
$testPdo->exec("CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, status TEXT);");

// Test: DB QueryBuilder Insert & Get
$total++;
$insertId = DB::table('users')->insert([
    'name' => 'Wahyu',
    'email' => 'wahyu@vioni.dev',
    'status' => 'active',
]);

$userRow = DB::table('users')->find($insertId);
if ($userRow && $userRow['name'] === 'Wahyu') {
    echo "✅ PASS: QueryBuilder Insert & Find by PK\n";
    $passed++;
} else {
    echo "❌ FAIL: QueryBuilder Insert & Find by PK\n";
}

// Test: QueryBuilder Update & Where & Count
$total++;
DB::table('users')->where('id', '=', $insertId)->update(['status' => 'verified']);
$count = DB::table('users')->where('status', '=', 'verified')->count();
if ($count === 1) {
    echo "✅ PASS: QueryBuilder Where, Update, and Count\n";
    $passed++;
} else {
    echo "❌ FAIL: QueryBuilder Where, Update, and Count\n";
}

// Test: Anti-SQL Injection (Disallows Malicious Identifier Injection)
$total++;
$injectionBlocked = false;
try {
    DB::table("users; DROP TABLE users;--")->get();
} catch (\InvalidArgumentException $e) {
    $injectionBlocked = true;
}

if ($injectionBlocked) {
    echo "✅ PASS: Anti-SQL Injection (Malicious Identifier Blocked)\n";
    $passed++;
} else {
    echo "❌ FAIL: Anti-SQL Injection Failed to block malicious table name\n";
}

// Test: Active Record Model (App\Models\User)
$total++;
$userModel = User::create([
    'name' => 'Vioni Developer',
    'email' => 'dev@vioni.dev',
    'status' => 'active',
]);

$fetchedUser = User::find($userModel->id);
if ($fetchedUser && $fetchedUser->email === 'dev@vioni.dev') {
    echo "✅ PASS: Active Record Model Create & Find\n";
    $passed++;
} else {
    echo "❌ FAIL: Active Record Model Create & Find\n";
}

// Test: Active Record Model Update & Delete
$total++;
$fetchedUser->name = 'Vioni Lead';
$fetchedUser->save();
$updatedUser = User::find($fetchedUser->id);
$isUpdated = $updatedUser->name === 'Vioni Lead';

$updatedUser->delete();
$isDeleted = User::find($fetchedUser->id) === null;

if ($isUpdated && $isDeleted) {
    echo "✅ PASS: Active Record Model Save & Delete\n";
    $passed++;
} else {
    echo "❌ FAIL: Active Record Model Save & Delete\n";
}

// Test: Database ACID Transaction
$total++;
$txSuccess = false;
try {
    DB::transaction(function () {
        DB::table('users')->insert(['name' => 'Tx Test', 'email' => 'tx@vioni.dev', 'status' => 'pending']);
        throw new \Exception("Simulated transaction rollback");
    });
} catch (\Exception $e) {
    // Check if inserted record was safely rolled back
    $txRow = DB::table('users')->where('email', 'tx@vioni.dev')->first();
    $txSuccess = ($txRow === null);
}

if ($txSuccess) {
    echo "✅ PASS: ACID Database Transaction Rollback on Exception\n";
    $passed++;
} else {
    echo "❌ FAIL: ACID Database Transaction Rollback failed\n";
}

echo "\nResult: {$passed}/{$total} tests passed.\n";
if ($passed === $total) {
    echo "🎉 All framework core, middleware, cybersecurity, and database tests passed flawlessly!\n";
}
