<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\BindSessionActor;

final class RegisterActorSession
{
    private $registry;

    public function __construct($registry = null)
    {
        $this->registry = $registry;
    }

    public function handle(string $actorId) : void
    {
        $this->registry?->register($actorId);
    }
}