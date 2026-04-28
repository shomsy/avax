<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Support;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\SessionStoreInterface;

/**
 * Deterministic in-memory session store for session identity tests.
 */
final class ArraySessionStore implements SessionStoreInterface
{
    /** @var array<string, mixed> */
    private array $values = [];

    private int $sequence = 0;

    private string|null $sessionId = null;

    public function regenerate() : string
    {
        $this->sessionId = 'session-' . ++$this->sequence;

        return $this->sessionId;
    }

    public function id() : string|null
    {
        return $this->sessionId;
    }

    public function get(string $key) : mixed
    {
        return $this->values[$key] ?? null;
    }

    public function put(string $key, mixed $value) : void
    {
        $this->start();
        $this->values[$key] = $value;
    }

    public function start() : void
    {
        $this->sessionId ??= 'session-' . ++$this->sequence;
    }

    public function forget(string $key) : void
    {
        unset($this->values[$key]);
    }

    public function invalidate() : void
    {
        $this->values    = [];
        $this->sessionId = null;
    }
}
