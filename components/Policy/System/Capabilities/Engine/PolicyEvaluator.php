<?php

declare(strict_types=1);

namespace Avax\Components\Policy\System\Capabilities\Engine;

use Avax\Components\Policy\System\Capabilities\Rules\PolicyRule;

final class PolicyEvaluator
{
    /** @var list<PolicyRule> */
    private array $rules = [];

    public function register(PolicyRule $rule) : void
    {
        $this->rules[] = $rule;
    }

    public function explain(string $action, object $resource, array $context) : DecisionExplanation
    {
        $reasons = [];

        foreach ($this->rules as $rule) {
            if (! $rule->applies($action, $resource)) {
                continue;
            }

            $result = $rule->evaluate($action, $resource, $context);

            if ($result === null) {
                continue;
            }

            $reasons[] = $result->reason;
        }

        $allowed = ! in_array(false, array_column($reasons, 'allowed'));

        return new DecisionExplanation($allowed, $reasons);
    }

    public function evaluate(string $action, object $resource, array $context) : PolicyDecision
    {
        $reasons = [];

        foreach ($this->rules as $rule) {
            if (! $rule->applies($action, $resource)) {
                continue;
            }

            $result = $rule->evaluate($action, $resource, $context);

            if ($result === null) {
                continue;
            }

            if ($result->allowed) {
                $reasons[] = $result->reason;
            } else {
                return PolicyDecision::deny($result->reason);
            }
        }

        return PolicyDecision::allow(
            $reasons ? 'Matched ' . count($reasons) . ' rules' : null,
        );
    }
}

final readonly class PolicyDecision
{
    public function __construct(
        public bool        $allowed,
        public string|null $reason,
    ) {}
}