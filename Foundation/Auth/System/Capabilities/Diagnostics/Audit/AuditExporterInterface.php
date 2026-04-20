<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Diagnostics;

interface AuditExporterInterface
{
    /**
     * @param list<AuditEvent> $events
     */
    public function export(array $events) : void;
}
