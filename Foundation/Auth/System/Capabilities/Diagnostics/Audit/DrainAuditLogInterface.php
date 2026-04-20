<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Diagnostics;

interface DrainAuditLogInterface extends AuditLogInterface
{
    /**
     * @return list<AuditEvent>
     */
    public function drain() : array;
}
