<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Flows\RecordDeadLetter;

use Avax\Components\Operations\Resilience\System\Capabilities\DeadLetter\DeadLetterStore;

final readonly class RecordDeadLetter
{
    public function record(DeadLetterStore $store, string $message, string $reason, array $metadata = []) : void
    {
        $store->store($message, $metadata, $reason);
    }
}
