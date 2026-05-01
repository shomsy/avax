<?php

declare(strict_types=1);

namespace Avax\Components\Security\System\System\Capabilities\Audit;

final class SecurityAuditLog
{
    /** @var list<array{event:string,context:array<string,mixed>,recorded_at:string}> */
    private array $events = [];

    public function record(string $event, array $context = []) : void
    {
        $this->events[] = [
            'event'       => $event,
            'context'     => $this->redact(context: $context),
            'recorded_at' => date(format: DATE_ATOM),
        ];
    }

    private function redact(array $context) : array
    {
        foreach (['password', 'token', 'secret'] as $key) {
            if (isset($context[$key])) {
                $context[$key] = '[redacted]';
            }
        }

        return $context;
    }

    public function all() : array
    {
        return $this->events;
    }
}
