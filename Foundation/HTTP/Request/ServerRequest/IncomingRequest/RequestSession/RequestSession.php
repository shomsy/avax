<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestSession;

/**
 * RequestSession - Capability Owner: Manages the session data attached to a request.
 *
 * Semantic rules:
 * - key presence uses `array_key_exists()`. A session value can be explicitly set to `null`.
 * - `has()` determines presence, regardless of nullability.
 */
final readonly class RequestSession
{
    public function __construct(
        private array   $data = [],
        private string|null $id = null
    ) {}

    public function id() : string|null
    {
        return $this->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function all() : array
    {
        return $this->data;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->data) 
            ? $this->data[$key] 
            : $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function put(string $key, mixed $value) : self
    {
        $newData       = $this->data;
        $newData[$key] = $value;

        return new self(data: $newData, id: $this->id);
    }

    public function forget(string $key) : self
    {
        $newData = $this->data;
        unset($newData[$key]);

        return new self(data: $newData, id: $this->id);
    }
}
