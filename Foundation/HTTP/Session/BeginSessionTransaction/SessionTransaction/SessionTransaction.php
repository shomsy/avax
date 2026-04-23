<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\BeginSessionTransaction;

use Avax\HTTP\Session\SessionStore\SessionStore;
use Exception;

final class SessionTransaction
{
    private SessionStore $store;
    private array|null   $backup = null;
    private bool         $active = false;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function begin() : void
    {
        if ($this->active) {
            throw new Exception('Transaction already active');
        }

        $this->backup = $this->store->all();
        $this->active = true;
    }

    public function commit() : void
    {
        if (! $this->active) {
            throw new Exception('No active transaction');
        }

        $this->backup = null;
        $this->active = false;
    }

    public function rollback() : void
    {
        if (! $this->active || $this->backup === null) {
            throw new Exception('No active transaction');
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