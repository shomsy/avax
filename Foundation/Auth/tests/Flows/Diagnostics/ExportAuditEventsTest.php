<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Diagnostics;

use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditExporterInterface;
use Avax\Auth\System\Flow\Diagnostics\ExportAuditEvents\ExportAuditEvents;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ExportAuditEventsTest extends TestCase
{
    public function testExportDrainsAuditEvents() : void
    {
        $log = new InMemoryAuditLog();
        $log->record(new AuditEvent('auth.login.succeeded', new \DateTimeImmutable()));
        $log->record(new AuditEvent('auth.logout.completed', new \DateTimeImmutable()));
        $capture = new stdClass();
        $capture->events = [];

        $count = (new ExportAuditEvents(
            auditLog: $log,
            exporter: new class($capture) implements AuditExporterInterface
            {
                /**
                 * @param list<AuditEvent> $events
                 */
                public function __construct(private stdClass $capture) {}

                public function export(array $events) : void
                {
                    $this->capture->events = $events;
                }
            }
        ))->execute();

        $this->assertSame(2, $count);
        $this->assertCount(2, $capture->events);
        $this->assertSame([], $log->events());
    }
}
