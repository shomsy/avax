<?php

declare(strict_types=1);

namespace components\HTTP\Session\SessionRegistry;

use SensitiveParameter;

final class RegisterActiveSession
{
    private $registry;

    public function __construct($registry)
    {
        $this->registry = $registry;
    }

    public function handle(#[SensitiveParameter] string $sessionId, array $metadata = []) : void
    {
        $this->registry->register($sessionId, $metadata);
    }
}