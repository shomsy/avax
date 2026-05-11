<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Transaction;

use Avax\Components\HTTP\Session\System\PublicSurface\SessionScope;
use RuntimeException;

final class SessionTransaction
{
    /** @var array<string, mixed>|null */
    private array|null $backup = null;

    private bool $active = false;

    public function __construct(
        private readonly SessionScope $sessionScope,
    ) {}

    public function begin() : void
    {
        if ($this->active) {
            throw new RuntimeException('Transaction already active');
        }

        $this->backup = $this->sessionScope->all();
        $this->active = true;
    }

    public function commit() : void
    {
        if (! $this->active) {
            throw new RuntimeException('No active transaction');
        }

        $this->backup = null;
        $this->active = false;
    }

    public function rollback() : void
    {
        if (! $this->active || $this->backup === null) {
            throw new RuntimeException('No active transaction');
        }

        $this->sessionScope->clear();

        foreach ($this->backup as $key => $value) {
            $this->sessionScope->set($key, $value);
        }

        $this->backup = null;
        $this->active = false;
    }

    public function isActive() : bool
    {
        return $this->active;
    }
}
