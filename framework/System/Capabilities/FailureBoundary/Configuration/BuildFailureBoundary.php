<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Configuration;

use Avax\Components\HTTP\Response\ResponseFactory;
use Avax\Components\Operations\Observability\System\Capabilities\Logging\Logger;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\CleanupAfterFailure\CleanupAfterFailure;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\ClassifyFailure\ClassifyFailure;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\MapFailureToResult\MapFailureToResult;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\ReportFailure\ReportFailure;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\ResolveFailurePolicy\ResolveFailurePolicy;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RetryFailedAction\RetryFailedAction;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RunFallbackAction\RunFallbackAction;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RunFailurePipeline\RunFailurePipeline;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\SendFailureToDeadLetter\SendFailureToDeadLetter;
use Avax\Framework\System\Capabilities\FailureBoundary\Flows\RunProtectedAction\RunProtectedAction;

/**
 * BuildFailureBoundary — Builder factory that assembles the failure boundary component.
 */
final readonly class BuildFailureBoundary
{
    /**
     * @param array<string, mixed> $config
     */
    public function build(array $config = [], ?Logger $logger = null): RunProtectedAction
    {
        $config = FailureBoundaryConfiguration::fromArray($config);

        $cleanup = new CleanupAfterFailure();
        $classify = new ClassifyFailure();
        $report = new ReportFailure($logger);
        $retry = new RetryFailedAction();
        $fallback = new RunFallbackAction();
        $deadLetter = new SendFailureToDeadLetter();
        $mapToResult = new MapFailureToResult(new ResponseFactory());
        $resolvePolicy = new ResolveFailurePolicy();

        $pipeline = new RunFailurePipeline(
            resolvePolicy: $resolvePolicy,
            classify: $classify,
            report: $report,
            retry: $retry,
            fallback: $fallback,
            mapToResult: $mapToResult,
            deadLetter: $deadLetter,
        );

        return new RunProtectedAction(
            pipeline: $pipeline,
            cleanup: $cleanup,
        );
    }
}
