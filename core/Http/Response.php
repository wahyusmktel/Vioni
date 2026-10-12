<?php

declare(strict_types=1);

namespace Vioni\Http;

use JsonSerializable;
use Vioni\Security\Headers as SecurityHeaders;

/**
 * Handles outgoing HTTP responses with automated security headers
 * and intelligent content serialization.
 */
class Response
{
    private string $content;
    private int $statusCode;
    /** @var array<string, string> */
    private array $headers = [];
    private SecurityHeaders $securityHeaders;

    public function __construct(
        string $content = '',
        int $statusCode = 200,
        array $headers = []
    ) {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->securityHeaders = new SecurityHeaders();
    }

    /**
     * Create or transform any value into a standardized Vioni Response.
     */
    public static function make(mixed $content, int $status = 200): self
    {
        if ($content instanceof self) {
            return $content;
        }

        // Automatic JSON serialization for arrays or JSON-serializable objects
        if (is_array($content) || $content instanceof JsonSerializable || $content instanceof \stdClass) {
            return self::json($content, $status);
        }

        // String, int, or other scalars converted to HTML/Text response
        return new self((string) $content, $status, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    /**
     * Create a JSON response.
     */
    public static function json(mixed $data, int $status = 200): self
    {
        $encoded = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return new self($encoded, $status, [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    /**
     * Create a plain text response.
     */
    public static function text(string $content, int $status = 200): self
    {
        return new self($content, $status, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    /**
     * Set a custom response header.
     */
    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set HTTP status code.
     */
    public function status(int $status): self
    {
        $this->statusCode = $status;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Send HTTP headers, status code, and content body to the client.
     */
    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->statusCode);

            // 1. Emit Default Cyber Security Headers
            $this->securityHeaders->apply();

            // 2. Emit Custom Response Headers
            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}", true);
            }
        }

        echo $this->content;
    }
}
