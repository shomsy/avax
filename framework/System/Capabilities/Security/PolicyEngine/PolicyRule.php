<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\PolicyEngine;

use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyAction;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyContext;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyDecision;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyEffect;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicyResource;
use Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation\PolicySubject;

final readonly class PolicyRule
{
    /**
     * @param callable(PolicySubject, PolicyAction, PolicyResource, PolicyContext): bool|null $condition
     */
    public function __construct(
        public string $name,
        public PolicyEffect $effect,
        public mixed $condition = null,
    ) {
    }

    public function matches(
        PolicySubject $subject,
        PolicyAction $action,
        PolicyResource $resource,
        PolicyContext $context,
    ): bool {
        if ($this->condition === null) {
            return true;
        }

        return ($this->condition)($subject, $action, $resource, $context);
    }
}
