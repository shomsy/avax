<?php

declare(strict_types=1);

namespace Avax\API\Contracts\System\Foundation\Failure;

use RuntimeException;

final class ApiContractInvalid extends RuntimeException
{
    public function __construct(
        string        $message,
        public string $contractId,
        public array  $violations = [],
    )
    {
        parent::__construct($message);
    }
}