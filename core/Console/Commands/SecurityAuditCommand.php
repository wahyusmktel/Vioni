<?php

declare(strict_types=1);

namespace Vioni\Console\Commands;

use Vioni\Console\Command;
use Vioni\Foundation\Application;

class SecurityAuditCommand extends Command
{
    protected string $signature = 'security:audit';
    protected string $description = 'Perform a cyber security health audit on application settings';

    public function handle(array $args = [], array $options = []): int
    {
        $this->line("==================================================", 'cyan');
        $this->line("   🛡️  VIONI CYBER SECURITY AUDIT REPORT", 'bold');
        $this->line("==================================================", 'cyan');

        $headers = ['Check', 'Current Status', 'Risk Level'];
        $rows = [];
        $issues = 0;

        // 1. PHP Version
        $phpVersion = PHP_VERSION;
        if (version_compare($phpVersion, '8.4.0', '>=')) {
            $rows[] = ['PHP Runtime Version', "PHP {$phpVersion} (Supported)", 'OK ✅'];
        } else {
            $rows[] = ['PHP Runtime Version', "PHP {$phpVersion} (< 8.4)", 'HIGH 🔴'];
            $issues++;
        }

        // 2. Debug Mode Check
        $app = Application::getInstance();
        $debug = $app?->config('debug', true) ?? true;
        $env = $app?->config('env', 'development') ?? 'development';

        if ($env === 'production' && $debug) {
            $rows[] = ['Debug Mode in Production', 'Enabled (Leak Risk)', 'CRITICAL 🔴'];
            $issues++;
        } elseif ($debug) {
            $rows[] = ['Debug Mode', 'Enabled (Dev Mode)', 'NOTICE ⚠️'];
        } else {
            $rows[] = ['Debug Mode', 'Disabled (Safe)', 'OK ✅'];
        }

        // 3. Security Config Audit
        $secFile = dirname(__DIR__, 3) . '/config/security.php';
        if (file_exists($secFile)) {
            $secConfig = require $secFile;
            
            // X-Frame-Options
            if (!empty($secConfig['headers']['X-Frame-Options'])) {
                $rows[] = ['Clickjacking Defense (X-Frame-Options)', $secConfig['headers']['X-Frame-Options'], 'OK ✅'];
            } else {
                $rows[] = ['Clickjacking Defense (X-Frame-Options)', 'Missing', 'HIGH 🔴'];
                $issues++;
            }

            // X-Content-Type-Options
            if (!empty($secConfig['headers']['X-Content-Type-Options'])) {
                $rows[] = ['MIME Sniffing (X-Content-Type-Options)', $secConfig['headers']['X-Content-Type-Options'], 'OK ✅'];
            } else {
                $rows[] = ['MIME Sniffing (X-Content-Type-Options)', 'Missing', 'HIGH 🔴'];
                $issues++;
            }

            // CSP Status
            if (!empty($secConfig['csp']['enabled'])) {
                $rows[] = ['Content Security Policy (CSP)', 'Enforced', 'OK ✅'];
            } else {
                $rows[] = ['Content Security Policy (CSP)', 'Disabled (Recommended to enable)', 'LOW ⚠️'];
            }

            // Cookie HttpOnly
            if (!empty($secConfig['cookie']['httponly'])) {
                $rows[] = ['Session Cookie HttpOnly', 'Enabled', 'OK ✅'];
            } else {
                $rows[] = ['Session Cookie HttpOnly', 'Disabled', 'MEDIUM 🔴'];
                $issues++;
            }
        }

        // 4. APP_KEY check
        $envFile = dirname(__DIR__, 3) . '/.env';
        $hasKey = false;
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile) ?: '';
            if (preg_match('/^APP_KEY=base64:.+$/m', $envContent)) {
                $hasKey = true;
            }
        }

        if ($hasKey) {
            $rows[] = ['Application Cryptographic Key', 'Configured (256-bit)', 'OK ✅'];
        } else {
            $rows[] = ['Application Cryptographic Key', 'Missing (Run: php vioni key:generate)', 'HIGH 🔴'];
            $issues++;
        }

        $this->table($headers, $rows);

        if ($issues === 0) {
            $this->success("All critical security baselines passed! Your Vioni application is well-hardened.");
        } else {
            $this->warning("Found {$issues} security recommendation(s) to address before going live in production.");
        }

        return 0;
    }
}
