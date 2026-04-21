<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Diagnostics\Audit;

interface DrainAuditLogInterface extends AuditLogInterface
{
    /**
     * @return list<AuditEvent>
     */
    public function drain() : array;
}
