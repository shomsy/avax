<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Diagnostics;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditExporterInterface;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\ExportAuditEvents\ExportAuditEvents;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use Avax\Tests\TestCase;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ExportAuditEventsTest extends TestCase
{
    public function testExportDrainsAuditEvents() : void
    {
        $log = new InMemoryAuditLog();
        $log->record(event: new AuditEvent(name: 'auth.login.succeeded', occurredAt: new DateTimeImmutable()));
        $log->record(event: new AuditEvent(name: 'auth.logout.completed', occurredAt: new DateTimeImmutable()));
        $capture         = new stdClass();
        $capture->events = [];

        $count = new ExportAuditEvents(
            auditLog: $log,
            exporter: new class($capture) implements AuditExporterInterface {
                          private stdClass $capture;

                          /**
                           * @param list<AuditEvent> $events
                           */
                          public function __construct(stdClass $capture)
                          {
                              $this->capture = $capture;
                          }

                          public function export(array $events) : void
                          {
                              $this->capture->events = $events;
                          }
                      }
        )->execute();

        $this->assertSame(expected: 2, actual: $count);
        $this->assertCount(expectedCount: 2, haystack: $capture->events);
        $this->assertSame(expected: [], actual: $log->events());
    }
}
