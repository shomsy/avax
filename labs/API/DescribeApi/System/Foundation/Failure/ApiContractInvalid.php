<?php

declare(strict_types=1);

namespace Avax\Labs\API\DescribeApi\System\Foundation\Failure;

use RuntimeException;

final class ApiContractInvalid extends RuntimeException
{
    public function __construct(
        string      $field,
        string|null $reason = null,
    )
    {
        parent::__construct(
            $reason !== null
                ? "API contract invalid: {$field} - {$reason}"
                : "API contract invalid: {$field}"
        );
    }
}
