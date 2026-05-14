<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit;

interface AuditExporterInterface
{
    /**
     * @param list<AuditEvent> $events
     */
    public function export(array $events) : void;
}
