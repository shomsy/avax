<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\Foundation\Failure;

final class InvalidDateTimeString extends DateTimeFailure
{
    public function __construct(
        string $input,
        string $reason,
    ) {
        parent::__construct(
            message: sprintf("Invalid datetime string '%s': %s", $input, $reason),
        );
    }
}
