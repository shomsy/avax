<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionRegistry;

final class RegisterActiveSession
{
    private $registry;

    public function __construct($registry)
    {
        $this->registry = $registry;
    }

    public function handle(string $sessionId, array $metadata = []) : void
    {
        $this->registry->register($sessionId, $metadata);
    }
}