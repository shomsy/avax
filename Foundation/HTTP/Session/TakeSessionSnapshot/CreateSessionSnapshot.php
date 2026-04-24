<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\TakeSessionSnapshot;

use Avax\HTTP\Session\SessionStore\SessionStore;

final class CreateSessionSnapshot
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle() : array
    {
        return [
            'data'      => $this->store->all(),
            'timestamp' => time(),
        ];
    }
}