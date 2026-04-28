<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\RegenerateSessionId;

use Avax\Components\HTTP\Session\SessionStore\SessionStore;
use Random\RandomException;

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

    /**
     * @throws RandomException
     */
    private function generate() : string
    {
        return bin2hex(random_bytes(32));
    }
}