<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\BindSessionActor;

use Avax\HTTP\Session\SessionStore\SessionStore;

final class BindSessionActor
{
    private SessionStore $store;

    public function __construct(SessionStore $store) { $this->store = $store; }

    public function handle(string $actorId) : void { $this->store->put(key: 'actor_id', value: $actorId); }
}
