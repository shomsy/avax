<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Delivery\System\Capabilities\Health;

class DeliveryHealthReport
{
    public function __construct(
        public readonly bool $healthy,
        public readonly string $message,
        public readonly array $details = [],
    ) {
    }
}
