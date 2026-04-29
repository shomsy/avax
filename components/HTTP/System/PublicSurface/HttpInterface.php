<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\System\PublicSurface;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;

interface HttpInterface {
    public function handle(RequestInterface $r): ResponseInterface;
    public function terminate(RequestInterface $r, ResponseInterface $res): void;
}