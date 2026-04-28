<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Flows\ReadCurrentUser;

use Avax\Components\Auth\System\PublicSurface\Auth;

final class ReadCurrentUser
{
    public function __construct(
        private readonly Auth $auth,
    ) {}

    public function user() : mixed
    {
        return $this->auth->user();
    }
}