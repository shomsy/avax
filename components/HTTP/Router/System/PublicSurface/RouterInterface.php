<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\PublicSurface;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Files\Registrar;

interface RouterInterface
{
    public function get(string $path, mixed $action) : Registrar;

    public function post(string $path, mixed $action) : Registrar;

    public function put(string $path, mixed $action) : Registrar;

    public function patch(string $path, mixed $action) : Registrar;

    public function delete(string $path, mixed $action) : Registrar;

    public function options(string $path, mixed $action) : Registrar;

    public function head(string $path, mixed $action) : Registrar;

    public function dispatch(RequestInterface $request) : ResponseInterface;
}
