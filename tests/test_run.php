<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Middleware\VerifyCsrfToken;
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
    public function handle(Request $request, Closure $next): Response
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

echo "\nResult: {$passed}/" . count($testCases) . " tests passed.\n";
if ($passed === count($testCases)) {
    echo "🎉 All framework core, middleware, and cybersecurity tests passed flawlessly!\n";
}
