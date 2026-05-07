<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Flows\ApplyBulkhead;

use Avax\Components\Operations\Resilience\System\Capabilities\Bulkhead\Bulkhead;

final readonly class ApplyBulkhead
{
    public function apply(Bulkhead $bulkhead, callable $operation) : mixed
    {
        return $bulkhead->run($operation);
    }
}
