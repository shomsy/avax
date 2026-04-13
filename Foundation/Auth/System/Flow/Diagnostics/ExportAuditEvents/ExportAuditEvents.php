<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Diagnostics\ExportAuditEvents;

use Avax\Auth\System\Flow\Diagnostics\AuditExporterInterface;
use Avax\Auth\System\Flow\Diagnostics\DrainAuditLogInterface;

final readonly class ExportAuditEvents
{
    public function __construct(
        private DrainAuditLogInterface $auditLog,
        private AuditExporterInterface $exporter
    ) {}

    public function execute() : int
    {
        $events = $this->auditLog->drain();

        if ($events === []) {
            return 0;
        }

        $this->exporter->export(events: $events);

        return count($events);
    }
}
