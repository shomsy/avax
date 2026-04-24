<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Security;

interface SessionContextInterface
{
    public function sessionId() : string|null;

    public function userId() : int|string|null;
}
