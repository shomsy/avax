<?php

declare(strict_types=1);

namespace Avax\DateTime\System\Foundation\Failure;

final class InvalidDateTimeString extends DateTimeFailure
{
    public function __construct(
        string $input,
        string $reason,
    )
    {
        parent::__construct(
            message: "Invalid datetime string '{$input}': {$reason}",
        );
    }
}