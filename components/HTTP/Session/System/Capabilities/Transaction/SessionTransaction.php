<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Transaction;

use Avax\Components\HTTP\Session\System\Capabilities\Storage\SessionStoreInterface;
use RuntimeException;

final class SessionTransaction
{
    private ?array $backup = null;
    private bool   $active = false;

    public function __construct(
        private readonly SessionStoreInterface $store
    ) {}

    public function begin() : void
    {
        if ($this->active) {
            throw new RuntimeException('Transaction already active');
        }

        $this->backup = $this->store->all();
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

        $this->store->flush();

        foreach ($this->backup as $key => $value) {
            $this->store->put($key, $value);
        }

        $this->backup = null;
        $this->active = false;
    }

    public function isActive() : bool
    {
        return $this->active;
    }
}
