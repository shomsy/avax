<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Health\Foundation;

final readonly class HealthReport
{
    public function __construct(
        /** @var list<HealthFinding> $findings */
        public array $findings = [],
        public HealthStatus $overall = HealthStatus::Green,
    ) {
    }

    public static function healthy(string $message = 'OK'): self
    {
        return new self(
            findings: [new HealthFinding('process', HealthStatus::Green, $message)],
            overall: HealthStatus::Green,
        );
    }

    public static function unhealthy(string $message = ''): self
    {
        return new self(
            findings: [new HealthFinding('process', HealthStatus::Red, $message)],
            overall: HealthStatus::Red,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->overall->value,
            'findings' => array_map(
                static fn (HealthFinding $f) => [
                    'check' => $f->check,
                    'status' => $f->status->value,
                    'message' => $f->message,
                ],
                $this->findings,
            ),
        ];
    }
}
