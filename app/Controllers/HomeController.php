<?php

declare(strict_types=1);

namespace App\Controllers;

use Vioni\Http\Request;

class HomeController
{
    /**
     * Welcome page action.
     */
    public function index(): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Vioni PHP Framework</title>
            <style>
                body {
                    font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                    background: #0f172a;
                    color: #f8fafc;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    margin: 0;
                }
                .card {
                    background: #1e293b;
                    border: 1px solid #334155;
                    border-radius: 1rem;
                    padding: 2.5rem;
                    max-width: 580px;
                    text-align: center;
                    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4);
                }
                h1 {
                    font-size: 2.5rem;
                    margin-bottom: 0.5rem;
                    background: linear-gradient(135deg, #38bdf8 0%, #818cf8 100%);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                }
                p {
                    color: #94a3b8;
                    line-height: 1.6;
                }
                .badge {
                    display: inline-block;
                    padding: 0.25rem 0.75rem;
                    background: #0369a1;
                    color: #e0f2fe;
                    border-radius: 9999px;
                    font-size: 0.75rem;
                    font-weight: 600;
                    margin-bottom: 1rem;
                }
                .features {
                    margin-top: 1.5rem;
                    text-align: left;
                    background: #0f172a;
                    padding: 1rem 1.5rem;
                    border-radius: 0.5rem;
                    font-size: 0.9rem;
                    color: #cbd5e1;
                }
                .features li {
                    margin-bottom: 0.5rem;
                }
            </style>
        </head>
        <body>
            <div class="card">
                <span class="badge">Milestone 1 Active</span>
                <h1>Vioni Framework</h1>
                <p>Framework PHP modern, bersih, type-safe, dan mengedepankan keamanan siber.</p>
                <div class="features">
                    <ul style="margin: 0; padding-left: 1.2rem;">
                        <li><strong>PHP 8.4 Native:</strong> Zero-magic, explicit, full IDE autocompletion.</li>
                        <li><strong>Cyber Security by Default:</strong> OWASP Security Headers & Null-byte defense aktif.</li>
                        <li><strong>Auto-Serialization:</strong> Return array/object otomatis jadi JSON.</li>
                    </ul>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * User profile demo endpoint.
     */
    public function profile(Request $request, int $id): array
    {
        return [
            'status' => 'success',
            'framework' => 'Vioni Framework',
            'user' => [
                'id' => $id,
                'name' => 'Developer',
                'client_ip' => $request->ip(),
            ],
            'security' => [
                'headers_enforced' => true,
                'type_cast_verified' => true,
            ]
        ];
    }
}
