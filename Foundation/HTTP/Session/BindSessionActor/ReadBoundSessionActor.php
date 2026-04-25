<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\BindSessionActor;

use Avax\HTTP\Session\SessionStore\SessionStore;

final class ReadBoundSessionActor
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle() : SessionActor|null
    {
        $data = $this->store->get(key: '_actor');

        if (! is_array($data) || ! isset($data['id'])) {
            return null;
        }

        return new SessionActor(id: $data['id'], data: $data['data'] ?? []);
    }
}