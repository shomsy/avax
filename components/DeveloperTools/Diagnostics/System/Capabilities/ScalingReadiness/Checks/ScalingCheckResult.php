<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Diagnostics\System\Capabilities\ScalingReadiness\Checks;

final readonly class ScalingCheckResult
{
    public function __construct(
        public string $name,
        public bool $passed,
        public string $message,
    ) {}

    public function toArray(): array
    {
        return [
            'name'   => $this->name,
            'passed' => $this->passed,
            'message' => $this->message,
        ];
    }
}
