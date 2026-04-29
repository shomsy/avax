<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\System\Capabilities\Kernel;

use Avax\Components\HTTP\System\PublicSurface\HttpInterface;

final class HttpKernel
{
    public function __construct(
        private HttpInterface $http
    ) {}

    public function http(): HttpInterface
    {
        return $this->http;
    }
}
