<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\MapFailureToResult;

use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureAction;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureBoundaryKind;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePipelineResult;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePolicy;
use Throwable;

/**
 * MapFailureToResult — Maps a failure to a response or result.
 *
 * For HTTP context: produces a ResponseInterface with the configured status code.
 * For other contexts: returns the FailureAction metadata for downstream handling.
 */
final readonly class MapFailureToResult
{
    public function __construct(
        private CreateHttpResponse $createHttpResponse,
    ) {
    }

    public function execute(
        Throwable $failure,
        FailureContext $context,
        FailurePolicy $policy,
    ): FailurePipelineResult {
        $action = $policy->findAction($failure::class);

        if ($action === null) {
            // No specific action — use default error response
            return $this->defaultErrorResponse($failure, $context);
        }

        if ($context->kind === FailureBoundaryKind::Http) {
            $statusCode = $action->statusCode ?? 500;
            $message = $action->messageKey ?? 'An error occurred';
            $response = $this->createHttpResponse->error(message: $message, status: $statusCode);
            return FailurePipelineResult::mapped($response);
        }

        // Non-HTTP context: return action metadata
        return FailurePipelineResult::mapped([
            'action' => $action->decision->value,
            'status_code' => $action->statusCode,
            'message_key' => $action->messageKey,
            'exception' => $failure::class,
        ]);
    }

    private function defaultErrorResponse(Throwable $failure, FailureContext $context): FailurePipelineResult
    {
        if ($context->kind === FailureBoundaryKind::Http) {
            $response = $this->createHttpResponse->error(message: 'Internal server error', status: 500);
            return FailurePipelineResult::mapped($response);
        }

        return FailurePipelineResult::mapped([
            'exception' => $failure::class,
            'message' => $failure->getMessage(),
        ]);
    }
}
