<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Capabilities\Message;

use InvalidArgumentException;

final class ValidateStatusCode
{
    public function __invoke(int $statusCode) : int
    {
        if ($statusCode < 100 || $statusCode > 599) {
            throw new InvalidArgumentException(message: "Invalid HTTP status code [{$statusCode}].");
        }

        return $statusCode;
    }
}
