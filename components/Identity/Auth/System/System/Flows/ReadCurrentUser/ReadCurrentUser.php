<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Flows\ReadCurrentUser;

use Avax\Components\Identity\Auth\System\System\PublicSurface\Auth;

final readonly class ReadCurrentUser
{
    public function __construct(
        private Auth $auth,
    ) {}

    public function user() : mixed
    {
        return $this->auth->user();
    }
}
