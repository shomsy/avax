<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RunRecoveryAction;

use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureHandler;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePipelineResult;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePolicy;
use RuntimeException;
use Throwable;

/**
 * RunRecoveryAction — Executes a recovery handler declared via #[RecoverWith].
 *
 * Recovery is a specialized form of fallback focused on recovery behavior
 * rather than degraded operation. The handler receives the failure and context
 * and must return a recovered result.
 */
final readonly class RunRecoveryAction
{
    /**
     * @throws RuntimeException When no recovery class is configured, class not found, or class lacks __invoke
     */
    public function execute(
        Throwable      $failure,
        FailureContext $context,
        FailurePolicy  $policy,
    ) : mixed
    {
        $recoverClass = $policy->recoverWithClass;

        if ($recoverClass === null) {
            throw new RuntimeException('No recovery class configured');
        }

        if (! class_exists($recoverClass)) {
            throw new RuntimeException("Recovery class not found: {$recoverClass}");
        }

        if (! is_subclass_of($recoverClass, FailureHandler::class)) {
            throw new RuntimeException("Recovery class must implement " . FailureHandler::class . ": {$recoverClass}");
        }

        $handler = new $recoverClass();

        $result = $handler($failure, $context);

        return FailurePipelineResult::recovered($result);
    }
}
