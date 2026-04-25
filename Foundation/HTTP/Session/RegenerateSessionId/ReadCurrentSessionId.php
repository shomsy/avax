<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\RegenerateSessionId;

use Avax\HTTP\Session\SessionStore\SessionStore;

final class ReadCurrentSessionId
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle() : string
    {
        return $this->store->get(key: '_session_id', default: '') ?: $this->generate();
    }

    private function generate() : string
    {
        return bin2hex(random_bytes(32));
    }
}