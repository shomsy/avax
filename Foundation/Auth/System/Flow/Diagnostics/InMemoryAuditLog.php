<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Diagnostics;

/**
 * In-memory audit sink for tests.
 */
final class InMemoryAuditLog implements AuditLogInterface
{
    /** @var list<AuditEvent> */
    private array $events = [];

    public function record(AuditEvent $event) : void
    {
        $this->events[] = $event;
    }

    /**
     * @return list<AuditEvent>
     */
    public function events() : array
    {
        return $this->events;
    }
}
