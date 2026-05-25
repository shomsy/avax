<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\Policy;

use Avax\Components\Identity\Access\System\Capabilities\Policy\Engine\PolicyEvaluator;
use Avax\Components\Identity\Access\System\Capabilities\Policy\Foundation\DecisionExplanation;
use Avax\Components\Identity\Access\System\Capabilities\Policy\Foundation\PolicyDecision;
use Avax\Components\Identity\Access\System\Capabilities\Policy\Rules\PolicyRule;

final class Policy
{
    private static PolicyEvaluator $policyEvaluator;

    private static array $definitions = [];

    public static function define(string $name, array $rules) : void
    {
        self::$definitions[$name] = $rules;
    }

    public static function authorize(string $name, array $context) : bool
    {
        $rules = self::$definitions[$name] ?? [];

        foreach ($rules as $key => $expected) {
            $actual = $context[$key] ?? null;

            if ($actual !== $expected) {
                return false;
            }
        }

        return true;
    }

    public static function allows(string $action, object $resource, array $context = []) : PolicyDecision
    {
        return self::evaluator()->evaluate($action, $resource, $context);
    }

    private static function evaluator() : PolicyEvaluator
    {
        if (! isset(self::$policyEvaluator)) {
            self::$policyEvaluator = new PolicyEvaluator();
        }

        return self::$policyEvaluator;
    }

    public static function register(PolicyRule $policyRule) : void
    {
        self::evaluator()->register($policyRule);
    }

    public static function explain(string $action, object $resource, array $context = []) : DecisionExplanation
    {
        return self::evaluator()->explain($action, $resource, $context);
    }

    /**
     * Reset static state for long-lived worker safety.
     * MUST be called between requests in persistent runtimes.
     */
    public static function reset() : void
    {
        unset(self::$policyEvaluator);
        self::$definitions = [];
    }

    /**
     * Replace the policy evaluator (for test injection).
     */
    public static function setEvaluator(PolicyEvaluator $evaluator) : void
    {
        self::$policyEvaluator = $evaluator;
    }

    /**
     * Replace policy definitions (for test injection).
     *
     * @param array<string, array<string, mixed>> $definitions
     */
    public static function setDefinitions(array $definitions) : void
    {
        self::$definitions = $definitions;
    }
}
