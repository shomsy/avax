<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RunFailurePipeline;

use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\ClassifyFailure\ClassifyFailure;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\MapFailureToResult\MapFailureToResult;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\ReportFailure\ReportFailure;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\ResolveFailurePolicy\ResolveFailurePolicy;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RetryFailedAction\RetryFailedAction;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RunFallbackAction\RunFallbackAction;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\SendFailureToDeadLetter\SendFailureToDeadLetter;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureDecision;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\UnhandledFailure;
use Closure;
use Throwable;

/**
 * RunFailurePipeline — Executes the failure decision pipeline.
 */
final readonly class RunFailurePipeline
{
    public function __construct(
        private ResolveFailurePolicy $resolvePolicy,
        private ClassifyFailure $classify,
        private ReportFailure $report,
        private RetryFailedAction $retry,
        private RunFallbackAction $fallback,
        private MapFailureToResult $mapToResult,
        private SendFailureToDeadLetter $deadLetter,
    ) {
    }

    public function for(Throwable $failure, FailureContext $context, Closure $originalAction): mixed
    {
        $policy = $this->resolvePolicy->for($context);
        $decision = $this->classify->decide($failure, $policy);

        $this->report->for($failure, $context, $policy);

        return match ($decision) {
            FailureDecision::Retry => $this->retry->execute(
                failure: $failure,
                context: $context,
                policy: $policy,
                originalAction: $originalAction,
            ),
            FailureDecision::Fallback => $this->fallback->execute($failure, $context, $policy),
            FailureDecision::MapToResult => $this->mapToResult->execute($failure, $context, $policy),
            FailureDecision::DeadLetter => $this->deadLetter->send($failure, $context, $policy),
            FailureDecision::Rethrow => throw $failure,
            FailureDecision::ReportOnly => throw new UnhandledFailure($failure, $context),
        };
    }
}
