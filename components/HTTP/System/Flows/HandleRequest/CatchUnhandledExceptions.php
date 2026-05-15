<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Flows\HandleRequest;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\SecureRequest\System\Foundation\Failure\SecureRequestAuthorizationFailed;
use Avax\Components\HTTP\SecureRequest\System\Foundation\Failure\SecureRequestResolutionFailed;
use Avax\Components\HTTP\SecureRequest\System\Foundation\Failure\SecureRequestValidationFailed;
use Exception;

final readonly class CatchUnhandledExceptions
{
    public function __construct(
        private ReportExceptionToLogger $reportExceptionToLogger,
        private CreateHttpResponse      $createHttpResponse,
    ) {}

    public function handle(Exception $exception, RequestInterface $request) : ResponseInterface
    {
        // SecureRequest validation failure -> 422 with field errors
        if ($exception instanceof SecureRequestValidationFailed) {
            $errors = [];
            if ($exception->violations !== null) {
                foreach ($exception->violations as $violation) {
                    $errors[$violation->field][] = $violation->message;
                }
            }

            return $this->createHttpResponse->json(
                data  : ['message' => 'The given data was invalid.', 'errors' => $errors],
                status: 422,
            );
        }

        // SecureRequest authorization failure -> 403
        if ($exception instanceof SecureRequestAuthorizationFailed) {
            return $this->createHttpResponse->json(
                data  : ['message' => 'This action is not authorized.'],
                status: 403,
            );
        }

        // SecureRequest resolution failure -> 500
        if ($exception instanceof SecureRequestResolutionFailed) {
            $this->reportExceptionToLogger->report($exception, $request);

            return $this->createHttpResponse->json(
                data  : ['error' => 'Internal Server Error', 'message' => $exception->getMessage()],
                status: 500,
            );
        }

        // Generic unhandled exception -> 500
        $this->reportExceptionToLogger->report($exception, $request);

        return $this->createHttpResponse->json(
            data  : ['error' => 'Internal Server Error', 'message' => $exception->getMessage()],
            status: 500,
        );
    }
}
