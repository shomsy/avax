<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware\System\PublicSurface;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;

interface MiddlewareInterface { public function handle(RequestInterface $r, callable $n): ResponseInterface; }
