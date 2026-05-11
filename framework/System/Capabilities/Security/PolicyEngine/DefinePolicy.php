<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\PolicyEngine;

use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyAction;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyContext;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyEffect;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyResource;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicySubject;

final class DefinePolicy
{
    /** @var list<PolicyRule> */
    private array $rules = [];
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param callable(PolicySubject, PolicyAction, PolicyResource, PolicyContext): bool|null $condition
     */
    public function allow(string $ruleName, callable|null $condition = null) : self
    {
        $this->rules[] = new PolicyRule($ruleName, PolicyEffect::Allow, $condition);

        return $this;
    }

    /**
     * @param callable(PolicySubject, PolicyAction, PolicyResource, PolicyContext): bool|null $condition
     */
    public function deny(string $ruleName, callable|null $condition = null) : self
    {
        $this->rules[] = new PolicyRule($ruleName, PolicyEffect::Deny, $condition);

        return $this;
    }

    /**
     * @return list<PolicyRule>
     */
    public function rules(): array
    {
        return $this->rules;
    }
}
