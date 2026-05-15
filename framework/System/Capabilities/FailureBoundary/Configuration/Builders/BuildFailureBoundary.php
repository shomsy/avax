<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Configuration\Builders;

use Avax\Framework\System\Capabilities\FailureBoundary\Configuration\FailureBoundaryConfiguration;

use Avax\Components\HTTP\System\Capabilities\ResponseBuilding\ResponseFactory;
use Avax\Components\Operations\Observability\System\Capabilities\Logging\Logger;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs\FailedJobsStore;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\ClassifyFailure\ClassifyFailure;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\CleanupAfterFailure\CleanupAfterFailure;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\CleanupAfterFailure\FailureCleanupRegistry;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\EnforceTimeout\EnforceTimeout;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\MapFailureToResult\MapFailureToResult;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\ReportFailure\ReportFailure;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\ResolveFailurePolicy\ResolveFailurePolicy;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RetryFailedAction\RetryFailedAction;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RunFailurePipeline\RunFailurePipeline;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RunFallbackAction\RunFallbackAction;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RunRecoveryAction\RunRecoveryAction;
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
    public function build(array $config = [], ?Logger $logger = null, ?FailedJobsStore $failedJobsStore = null) : RunProtectedAction
    {
        $config = FailureBoundaryConfiguration::fromArray($config);

        $cleanup = new CleanupAfterFailure(registry: new FailureCleanupRegistry());
        $classify = new ClassifyFailure();
        $report = new ReportFailure($logger);
        $retry = new RetryFailedAction();
        $fallback = new RunFallbackAction();
        $recovery = new RunRecoveryAction();
        $deadLetter = new SendFailureToDeadLetter($failedJobsStore);
        $mapToResult = new MapFailureToResult(new ResponseFactory());
        $resolvePolicy = new ResolveFailurePolicy();

        $pipeline = new RunFailurePipeline(
            resolvePolicy: $resolvePolicy,
            classify: $classify,
            report: $report,
            retry: $retry,
            fallback: $fallback,
            recovery     : $recovery,
            mapToResult: $mapToResult,
            deadLetter: $deadLetter,
        );

        return new RunProtectedAction(
            pipeline: $pipeline,
            cleanup: $cleanup,
            enforceTimeout: new EnforceTimeout(),
        );
    }
}
