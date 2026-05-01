<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\Policy\System\PublicSurface;

use Avax\Components\Identity\Access\System\Capabilities\Policy\System\Capabilities\Engine\PolicyEvaluator;
use Avax\Components\Identity\Access\System\Capabilities\Policy\System\Capabilities\Rules\PolicyRule;

final class Policy
{
    private static PolicyEvaluator $evaluator;
    private static array           $definitions = [];

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
        if (! isset(self::$evaluator)) {
            self::$evaluator = new PolicyEvaluator();
        }

        return self::$evaluator;
    }

    public static function register(PolicyRule $rule) : void
    {
        self::evaluator()->register($rule);
    }

    public static function explain(string $action, object $resource, array $context = []) : DecisionExplanation
    {
        return self::evaluator()->explain($action, $resource, $context);
    }
}

final readonly class PolicyDecision
{
    public function __construct(
        public bool        $allowed,
        public string|null $reason = null,
    ) {}

    public static function allow(string|null $reason = null) : self
    {
        return new self(true, $reason);
    }

    public static function deny(string|null $reason = null) : self
    {
        return new self(false, $reason);
    }
}

final readonly class DecisionExplanation
{
    /** @var list<string> */
    public array $reasons;

    public function __construct(
        public bool $allowed,
        array       $reasons = [],
    )
    {
        $this->reasons = $reasons;
    }

    public function toString() : string
    {
        return $this->allowed
            ? 'ALLOWED: ' . implode(' AND ', $this->reasons)
            : 'DENIED: ' . implode(' AND ', $this->reasons);
    }
}