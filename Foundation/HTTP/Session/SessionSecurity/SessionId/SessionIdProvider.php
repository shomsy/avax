<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity\SessionId;

interface SessionIdProvider
{
    public function getCurrentId() : string;

    public function rotate() : string;
}