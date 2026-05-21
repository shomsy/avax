<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\Policy\Rules;

use Avax\Components\Identity\Access\System\Capabilities\Policy\Foundation\PolicyDecision;
use Closure;

final readonly class PolicyRule
{
    public function __construct(
        public string  $action,
        public Closure $condition,
        public string  $reason,
    ) {}

    public function applies(string $action) : bool
    {
        return $this->action === $action;
    }

    public function evaluate(string $action, object $resource, array $context) : PolicyDecision|null
    {
        $result = ($this->condition)($resource, $context);

        if ($result === true) {
            return new PolicyDecision(true, $this->reason);
        }

        if ($result === false) {
            return new PolicyDecision(false, $this->reason);
        }

        return null;
    }
}
