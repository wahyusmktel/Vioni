<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Cyber Security Headers (OWASP Recommended)
    |--------------------------------------------------------------------------
    | Vioni automatically attaches these defense-in-depth headers to all
    | outgoing HTTP responses to mitigate MIME-sniffing, Clickjacking,
    | Information Disclosure, and Cross-Site Scripting (XSS).
    */
    'headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
        'X-Permitted-Cross-Domain-Policies' => 'none',
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy (CSP)
    |--------------------------------------------------------------------------
    | Restricts resources (such as JavaScript, CSS, Images) that the browser
    | is allowed to load for a given page.
    */
    'csp' => [
        'enabled' => false, // Set to true once developer configures directives
        'directives' => "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;",
    ],

    /*
    |--------------------------------------------------------------------------
    | Session & Cookie Security
    |--------------------------------------------------------------------------
    */
    'cookie' => [
        'httponly' => true,
        'secure' => false, // Set to true in production over HTTPS
        'samesite' => 'Lax', // 'Lax' | 'Strict' | 'None'
    ],
];
