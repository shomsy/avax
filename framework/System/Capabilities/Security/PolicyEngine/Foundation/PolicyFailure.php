<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation;

final readonly class PolicyFailure
{
    public function __construct(
        public string $subject,
        public string $action,
        public string $resource,
        public string $reason = 'Policy evaluation failed.',
    ) {
    }

    public function message(): string
    {
        return "Policy denied: '{$this->subject}' cannot '{$this->action}' on '{$this->resource}'. Reason: {$this->reason}";
    }
}
