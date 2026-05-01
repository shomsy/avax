<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Flows\HandleRequest;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\Flows\CreateJsonResponse\CreateJsonResponse;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Exception;

final class CatchUnhandledExceptions
{
    public function __construct(
        private ReportExceptionToLogger $logger,
        private CreateJsonResponse $responseFactory,
    ) {
    }

    public function handle(Exception $e, RequestInterface $request): ResponseInterface
    {
        $this->logger->report($e, $request);

        return $this->responseFactory->execute([
                                                   'error'   => 'Internal Server Error',
                                                   'message' => $e->getMessage(),
        ], 500);
    }
}
