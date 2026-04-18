<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestSession;

/**
 * Capability Owner: Manages the session data attached to a request.
 *
 * This follows the "Attached Capability" pattern.
 */
final readonly class RequestSession
{
    public function __construct(
        private array   $data = [],
        private ?string $id = null
    ) {}

    public function id() : ?string
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

    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function has(string $key) : bool
    {
        return isset($this->data[$key]);
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
