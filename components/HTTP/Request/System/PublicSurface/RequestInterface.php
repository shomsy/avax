<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\PublicSurface;

use Psr\Http\Message\ServerRequestInterface;

interface RequestInterface extends ServerRequestInterface
{
    public function input(string $key, mixed $default = null): mixed;
    public function all(): array;
}
