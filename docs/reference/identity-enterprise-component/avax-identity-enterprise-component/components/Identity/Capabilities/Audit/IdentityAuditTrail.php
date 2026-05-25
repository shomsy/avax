<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Audit;

interface IdentityAuditTrail
{
    public function record(IdentityEvent $event): void;
}
