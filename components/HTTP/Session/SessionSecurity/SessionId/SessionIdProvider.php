<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\SessionSecurity\SessionId;

interface SessionIdProvider
{
    public function getCurrentId() : string;

    public function rotate() : string;
}