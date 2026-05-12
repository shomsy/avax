<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\ClassifyFailure;

use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureAction;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureDecision;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePolicy;
use Throwable;

/**
 * ClassifyFailure — Classifies a failure against the policy rules to determine the decision.
 */
final readonly class ClassifyFailure
{
    public function decide(Throwable $failure, FailurePolicy $policy): FailureDecision
    {
        // Check if Rethrow applies
        foreach ($policy->rethrowExcept as $exceptClass) {
            if ($failure instanceof $exceptClass) {
                return FailureDecision::Rethrow;
            }
        }

        // Check for a matching OnFailure action
        $action = $policy->findAction($failure::class);
        if ($action !== null) {
            return $action->decision;
        }

        // Check for retry
        if ($policy->hasRetry()) {
            return FailureDecision::Retry;
        }

        // Check for recovery (RecoverWith)
        if ($policy->hasRecovery()) {
            return FailureDecision::Recover;
        }

        // Check for fallback
        if ($policy->hasFallback()) {
            return FailureDecision::Fallback;
        }

        // Check for dead letter
        if ($policy->hasDeadLetter()) {
            return FailureDecision::DeadLetter;
        }

        // Default: rethrow unhandled failure
        return FailureDecision::Rethrow;
    }
}
