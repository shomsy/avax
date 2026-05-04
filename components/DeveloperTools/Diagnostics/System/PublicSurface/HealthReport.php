<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Diagnostics\System\PublicSurface;

final readonly class HealthReport
{
    public function __construct(
        public string $status,
        /** @var array<string, CheckResult> */
        public array  $checks = []
    )
    {
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'checks' => array_map(
                static fn(CheckResult $checkResult): array => $checkResult->toArray(),
                $this->checks,
            ),
        ];
    }
}
