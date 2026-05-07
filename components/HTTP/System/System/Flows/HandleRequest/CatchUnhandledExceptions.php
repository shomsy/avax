<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\System\Flows\HandleRequest;

use Avax\Components\HTTP\Request\System\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\Flows\CreateJsonResponse\CreateJsonResponse;
use Avax\Components\HTTP\Response\System\System\PublicSurface\ResponseInterface;
use Exception;

final readonly class CatchUnhandledExceptions
{
    public function __construct(
        private ReportExceptionToLogger $reportExceptionToLogger,
        private CreateJsonResponse      $createJsonResponse,
    ) {}

    public function handle(Exception $exception, RequestInterface $request) : ResponseInterface
    {
        $this->reportExceptionToLogger->report($exception, $request);

        return $this->createJsonResponse->execute([
                                                      'error' => 'Internal Server Error',
                                                                                 'message' => $exception->getMessage(),
                                                  ], 500);
    }
}
