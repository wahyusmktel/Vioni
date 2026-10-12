<?php

declare(strict_types=1);

namespace Vioni\Security;

/**
 * Manages HTTP Security Headers to protect against common web vulnerabilities:
 * - Clickjacking (X-Frame-Options)
 * - MIME-type sniffing (X-Content-Type-Options)
 * - Referrer leakage (Referrer-Policy)
 * - Restrict unauthorized browser features (Permissions-Policy)
 */
class Headers
{
    /**
     * @var array<string, string>
     */
    private array $headers = [];

    public function __construct(array $customHeaders = [])
    {
        $defaultConfig = [];
        $configFile = dirname(__DIR__, 2) . '/config/security.php';
        if (file_exists($configFile)) {
            $config = require $configFile;
            $defaultConfig = $config['headers'] ?? [];
            if (!empty($config['csp']['enabled']) && !empty($config['csp']['directives'])) {
                $defaultConfig['Content-Security-Policy'] = $config['csp']['directives'];
            }
        }

        $this->headers = array_merge($defaultConfig, $customHeaders);
    }

    /**
     * Set or override a security header.
     */
    public function set(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Remove a security header.
     */
    public function remove(string $name): self
    {
        unset($this->headers[$name]);
        return $this;
    }

    /**
     * Retrieve all configured security headers.
     *
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->headers;
    }

    /**
     * Apply configured headers into PHP response.
     */
    public function apply(): void
    {
        if (headers_sent()) {
            return;
        }

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}", true);
        }
    }
}
