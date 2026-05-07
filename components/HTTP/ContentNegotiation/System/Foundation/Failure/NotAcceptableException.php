<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ContentNegotiation\System\Foundation\Failure;

use RuntimeException;

final class NotAcceptableException extends RuntimeException
{
    public function __construct(string $message = 'No acceptable media type found.')
    {
        parent::__construct($message, 406);
    }
}
