<?php

declare(strict_types=1);

namespace Avax\Components\Security\System\Capabilities\Audit;

final class SecurityAuditLog
{
    /** @var list<array{event:string,context:array<string,mixed>,recorded_at:string}> */
    private array $events = [];

    /**
     * @param array<string, mixed> $context
     */
    public function record(string $event, array $context = []): void
    {
        $this->events[] = [
            'event'   => $event,
            'context' => $this->redact(context: $context),
            'recorded_at' => date(format: DATE_ATOM),
        ];
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    private function redact(array $context): array
    {
        foreach (['password', 'token', 'secret'] as $key) {
            if (isset($context[$key])) {
                $context[$key] = '[redacted]';
            }
        }

        return $context;
    }

    /**
     * @return list<array{event:string,context:array<string,mixed>,recorded_at:string}>
     */
    public function all(): array
    {
        return $this->events;
    }
}
