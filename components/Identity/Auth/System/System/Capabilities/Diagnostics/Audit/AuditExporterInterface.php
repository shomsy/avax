<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Capabilities\Diagnostics\Audit;

interface AuditExporterInterface
{
    /**
     * @param list<AuditEvent> $events
     */
    public function export(array $events) : void;
}
