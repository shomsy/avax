<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Foundation\Failure;

use RuntimeException;

final class ApiSurfaceInvalid extends RuntimeException
{
    public function __construct(
        string  $field,
        ?string $reason = null,
    )
    {
        parent::__construct(
            $reason !== null
                ? "API contract invalid: {$field} - {$reason}"
                : "API contract invalid: {$field}"
        );
    }
}
