<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Flows\HandleRequest;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\System\PublicSurface\HttpInterface;
use Exception;

final readonly class HandleRequest
{
    public function __construct(
        private HttpInterface $http,
        private CatchUnhandledExceptions $catchUnhandledExceptions,
    ) {
    }

    public function execute(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->http->handle($request);
        } catch (Exception $exception) {
            return $this->catchUnhandledExceptions->handle($exception, $request);
        }
    }
}
