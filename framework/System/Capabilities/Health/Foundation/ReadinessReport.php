<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Health\Foundation;

final readonly class ReadinessReport
{
    public function __construct(
        public HealthStatus $status,
        /** @var list<HealthFinding> $checks */
        public array $checks = [],
        public string $version = '',
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'version' => $this->version,
            'checks' => array_map(
                static fn (HealthFinding $f) => [
                    'check' => $f->check,
                    'status' => $f->status->value,
                    'message' => $f->message,
                ],
                $this->checks,
            ),
        ];
    }
}
