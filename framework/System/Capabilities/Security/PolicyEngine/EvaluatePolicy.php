<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\PolicyEngine;

use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyAction;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyContext;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyDecision;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyEffect;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyFailure;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyResource;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicySubject;

final readonly class EvaluatePolicy
{
    /**
     * Evaluate a policy decision for a subject, action, and resource.
     *
     * Default is deny — if no rule matches, the decision is deny.
     */
    public function evaluate(
        DefinePolicy $policy,
        PolicySubject $subject,
        PolicyAction $action,
        PolicyResource $resource,
        PolicyContext $context,
    ): PolicyDecision {
        $lastMatchingDecision = null;

        foreach ($policy->rules() as $rule) {
            if ($rule->matches($subject, $action, $resource, $context)) {
                $lastMatchingDecision = $rule->effect === PolicyEffect::Allow
                    ? PolicyDecision::allow("Rule '{$rule->name}' allows.")
                    : PolicyDecision::deny("Rule '{$rule->name}' denies.");

                if ($rule->effect === PolicyEffect::Deny) {
                    return $lastMatchingDecision;
                }
            }
        }

        return $lastMatchingDecision ?? PolicyDecision::deny('No matching policy rule. Default deny.');
    }

    public function evaluateOrFail(
        DefinePolicy $policy,
        PolicySubject $subject,
        PolicyAction $action,
        PolicyResource $resource,
        PolicyContext $context,
    ): PolicyDecision {
        $decision = $this->evaluate($policy, $subject, $action, $resource, $context);

        if (!$decision->isAllowed()) {
            throw new PolicyDeniedException(new PolicyFailure(
                $subject->id,
                $action->name,
                "{$resource->type}:{$resource->identifier}",
                $decision->reason,
            ));
        }

        return $decision;
    }
}
