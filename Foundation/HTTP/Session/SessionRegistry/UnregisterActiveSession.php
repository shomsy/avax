<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionRegistry;

final class UnregisterActiveSession
{
    private $registry;

    public function __construct($registry)
    {
        $this->registry = $registry;
    }

    public function handle(string $sessionId) : bool
    {
        return $this->registry->unregister($sessionId);
    }
}