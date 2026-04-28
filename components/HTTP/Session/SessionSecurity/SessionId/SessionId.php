<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\SessionSecurity\SessionId;

use Random\RandomException;

final class SessionId
{
    public function __construct(
        private string $value,
        private int    $createdAt,
        private int    $lastActivity
    ) {}

    /**
     * @throws RandomException
     */
    public static function generate() : self
    {
        return new self(
            value       : bin2hex(random_bytes(32)),
            createdAt   : time(),
            lastActivity: time()
        );
    }

    public function value() : string
    {
        return $this->value;
    }

    public function createdAt() : int
    {
        return $this->createdAt;
    }

    public function lastActivity() : int
    {
        return $this->lastActivity;
    }

    public function refreshLastActivity() : void
    {
        $this->lastActivity = time();
    }
}