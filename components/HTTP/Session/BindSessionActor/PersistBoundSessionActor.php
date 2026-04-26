<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\BindSessionActor;

use Avax\HTTP\Session\SessionStore\SessionStore;

final class PersistBoundSessionActor
{
    private SessionStore $store;

    public function __construct(SessionStore $store)
    {
        $this->store = $store;
    }

    public function handle(SessionActor $actor) : void
    {
        $this->store->put(key: '_actor', value: ['id' => $actor->id(), 'data' => $actor->data()]);
    }
}