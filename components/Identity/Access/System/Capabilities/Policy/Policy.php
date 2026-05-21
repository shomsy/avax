<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\Policy;

use Avax\Components\Identity\Access\System\Capabilities\Policy\Engine\PolicyEvaluator;
use Avax\Components\Identity\Access\System\Capabilities\Policy\Foundation\DecisionExplanation;
use Avax\Components\Identity\Access\System\Capabilities\Policy\Foundation\PolicyDecision;
use Avax\Components\Identity\Access\System\Capabilities\Policy\Rules\PolicyRule;

final class Policy
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $definitions = [];

    public function __construct(
        private PolicyEvaluator $policyEvaluator,
    ) {}

    public function define(string $name, array $rules) : void
    {
        $this->definitions[$name] = $rules;
    }

    public function authorize(string $name, array $context) : bool
    {
        if (! isset($this->definitions[$name])) {
            return false;
        }

        $rules = $this->definitions[$name];

        foreach ($rules as $key => $expected) {
            $actual = $context[$key] ?? null;

            if ($actual !== $expected) {
                return false;
            }
        }

        return true;
    }

    public function allows(string $action, object $resource, array $context = []) : PolicyDecision
    {
        return $this->policyEvaluator->evaluate($action, $resource, $context);
    }

    public function register(PolicyRule $policyRule) : void
    {
        $this->policyEvaluator->register($policyRule);
    }

    public function explain(string $action, object $resource, array $context = []) : DecisionExplanation
    {
        return $this->policyEvaluator->explain($action, $resource, $context);
    }
}
