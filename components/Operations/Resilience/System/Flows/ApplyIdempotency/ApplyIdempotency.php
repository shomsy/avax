<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Flows\ApplyIdempotency;

use Avax\Components\Operations\Resilience\System\Capabilities\Idempotency\Idempotency;

final readonly class ApplyIdempotency
{
    public function apply(Idempotency $idempotency, string $key, callable $operation) : mixed
    {
        if ($idempotency->check($key)) {
            return $idempotency->replay($key);
        }

        $result = $operation();

        $idempotency->record($key, $result);

        return $result;
    }
}
