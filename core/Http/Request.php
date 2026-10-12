<?php

declare(strict_types=1);

namespace Vioni\Http;

/**
 * Encapsulates the incoming HTTP Request with built-in cybersecurity validation:
 * - Null-byte injection prevention
 * - Method spoofing validation
 * - Type-safe input getters
 * - JSON payload parsing
 */
class Request
{
    /**
     * @param array<string, mixed> $query ($_GET)
     * @param array<string, mixed> $post ($_POST)
     * @param array<string, mixed> $server ($_SERVER)
     * @param array<string, mixed> $cookies ($_COOKIE)
     * @param array<string, mixed> $files ($_FILES)
     * @param string|null $rawBody (php://input content)
     */
    public function __construct(
        private readonly array $query,
        private readonly array $post,
        private readonly array $server,
        private readonly array $cookies,
        private readonly array $files,
        private readonly ?string $rawBody = null,
        private ?array $jsonParsed = null,
    ) {
    }

    /**
     * Capture the incoming HTTP request from PHP globals.
     */
    public static function capture(): self
    {
        $raw = file_get_contents('php://input');

        return new self(
            query: $_GET,
            post: $_POST,
            server: $_SERVER,
            cookies: $_COOKIE,
            files: $_FILES,
            rawBody: $raw === false ? null : $raw
        );
    }

    /**
     * Get the sanitized HTTP request method.
     */
    public function method(): string
    {
        $method = strtoupper((string) ($this->server['REQUEST_METHOD'] ?? 'GET'));

        // Support for method spoofing via POST _method (e.g., PUT, DELETE, PATCH)
        if ($method === 'POST') {
            $override = $this->post['_method'] ?? $this->server['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? null;
            if (is_string($override)) {
                $override = strtoupper(trim($override));
                if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                    return $override;
                }
            }
        }

        return $method;
    }

    /**
     * Get the sanitized URI path without query strings and null bytes.
     */
    public function path(): string
    {
        $uri = (string) ($this->server['REQUEST_URI'] ?? '/');

        // Security: Remove null-byte poisonings
        $uri = str_replace("\0", '', $uri);

        // Strip query string
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        // Normalize leading and trailing slash
        $trimmed = trim($path, '/');
        return $trimmed === '' ? '/' : '/' . $trimmed;
    }

    /**
     * Get an input value from JSON or POST or GET data.
     */
    public function input(string $key, mixed $default = null): mixed
    {
        $all = $this->all();
        return $all[$key] ?? $default;
    }

    /**
     * Get input as integer (type-safe).
     */
    public function int(string $key, int $default = 0): int
    {
        $val = $this->input($key);
        return filter_var($val, FILTER_VALIDATE_INT) !== false ? (int) $val : $default;
    }

    /**
     * Get input as string (type-safe).
     */
    public function str(string $key, string $default = ''): string
    {
        $val = $this->input($key);
        return is_scalar($val) ? (string) $val : $default;
    }

    /**
     * Get input as boolean (type-safe).
     */
    public function bool(string $key, bool $default = false): bool
    {
        $val = $this->input($key);
        if ($val === null) {
            return $default;
        }
        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get all incoming data merged (JSON payload takes precedence, then POST, then Query).
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge($this->query, $this->post, $this->json());
    }

    /**
     * Parse and return JSON payload if Content-Type is application/json.
     *
     * @return array<string, mixed>
     */
    public function json(): array
    {
        if ($this->jsonParsed !== null) {
            return $this->jsonParsed;
        }

        if ($this->isJson() && !empty($this->rawBody)) {
            $decoded = json_decode($this->rawBody, true);
            if (is_array($decoded)) {
                return $this->jsonParsed = $decoded;
            }
        }

        return $this->jsonParsed = [];
    }

    /**
     * Check if request sent JSON payload.
     */
    public function isJson(): bool
    {
        $contentType = $this->header('Content-Type');
        return $contentType !== null && str_contains(strtolower($contentType), 'application/json');
    }

    /**
     * Get an incoming HTTP header by name (case-insensitive).
     */
    public function header(string $name, ?string $default = null): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

        if (isset($this->server[$key])) {
            return (string) $this->server[$key];
        }

        // Special headers not prefixed with HTTP_
        if (in_array(strtoupper($name), ['CONTENT-TYPE', 'CONTENT_TYPE'], true) && isset($this->server['CONTENT_TYPE'])) {
            return (string) $this->server['CONTENT_TYPE'];
        }

        if (in_array(strtoupper($name), ['CONTENT-LENGTH', 'CONTENT_LENGTH'], true) && isset($this->server['CONTENT_LENGTH'])) {
            return (string) $this->server['CONTENT_LENGTH'];
        }

        return $default;
    }

    /**
     * Get client IP address securely.
     */
    public function ip(): string
    {
        return (string) ($this->server['REMOTE_ADDR'] ?? '127.0.0.1');
    }
}
