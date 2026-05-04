<?php

declare(strict_types=1);

namespace Avax\API\System\Foundation\Failure;

use RuntimeException;

final class ApiFailure extends RuntimeException
{
    public function __construct(
        string        $message,
        public string $component = 'API',
    )
    {
        parent::__construct($message);
    }
}