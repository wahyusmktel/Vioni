<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Vioni\Foundation\Application;
use Vioni\Http\Request;

// Bootstrap application
/** @var Application $app */
$app = require dirname(__DIR__) . '/bootstrap/app.php';

$testCases = [
    [
        'name' => 'Home Page HTML',
        'server' => ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/'],
        'expectedStatus' => 200,
        'contains' => 'Vioni Framework',
    ],
    [
        'name' => 'Route with parameter (/hello/TJ)',
        'server' => ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/hello/TJ'],
        'expectedStatus' => 200,
        'contains' => 'Halo TJ!',
    ],
    [
        'name' => 'API Health JSON check',
        'server' => ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/health'],
        'expectedStatus' => 200,
        'contains' => '"status":"healthy"',
    ],
    [
        'name' => 'API Controller with Typed Param & Request DI (/api/users/42)',
        'server' => ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/users/42'],
        'expectedStatus' => 200,
        'contains' => '"id":42',
    ],
    [
        'name' => 'Route 404 Not Found',
        'server' => ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/not-exists'],
        'expectedStatus' => 404,
        'contains' => 'Not Found',
    ],
    [
        'name' => 'Route 405 Method Not Allowed',
        'server' => ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/'],
        'expectedStatus' => 405,
        'contains' => 'Method Not Allowed',
    ],
];

echo "=== VIONI FRAMEWORK TEST SUITE ===\n\n";

$passed = 0;
foreach ($testCases as $case) {
    $request = new Request(
        query: [],
        post: [],
        server: $case['server'],
        cookies: [],
        files: []
    );

    $response = $app->handle($request);
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
    echo "🎉 All initial core features working seamlessly!\n";
}
