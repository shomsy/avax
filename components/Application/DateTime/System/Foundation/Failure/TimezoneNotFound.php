<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\Foundation\Failure;

final class TimezoneNotFound extends DateTimeFailure
{
    public function __construct(
        string $timezone,
    )
    {
        parent::__construct(
            message: "Timezone '{$timezone}' not found or is invalid.",
        );
    }
}