<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\FlashSession;

use Avax\Components\HTTP\Session\SessionStore\SessionStore;

final class FlashSession
{
    private SessionStore $store;

    public function __construct(SessionStore $store) { $this->store = $store; }

    public function put(string $key, mixed $value) : void { $this->store->put(key: '_flash.' . $key, value: $value); }

    public function get(string $key, mixed $default = null) : mixed
    {
        $v = $this->store->get(key: '_flash.' . $key, default: $default);
        $this->store->delete(key: '_flash.' . $key);

        return $v;
    }
}
