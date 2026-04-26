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

    /**
     * @throws Exception
     */
    public function begin() : void
    {
        if ($this->active) {
            throw new Exception(message: 'Transaction already active');
        }

        $this->backup = $this->store->all();
        $this->active = true;
    }

    /**
     * @throws Exception
     */
    public function commit() : void
    {
        if (! $this->active) {
            throw new Exception(message: 'No active transaction');
        }

        $this->backup = null;
        $this->active = false;
    }

    /**
     * @throws Exception
     */
    public function rollback() : void
    {
        if (! $this->active || $this->backup === null) {
            throw new Exception(message: 'No active transaction');
        }

        $this->store->flush();

        foreach ($this->backup as $key => $value) {
            $this->store->put(key: $key, value: $value);
        }

        $this->backup = null;
        $this->active = false;
    }

    public function isActive() : bool
    {
        return $this->active;
    }
}