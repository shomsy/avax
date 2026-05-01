<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\PublicSurface;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;

interface RouterInterface
{
    public function get(string $u, mixed $a): void;

    public function post(string $u, mixed $a): void;

    public function dispatch(RequestInterface $request) : ResponseInterface;
}
