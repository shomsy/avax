<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Capabilities\Redirects;

use InvalidArgumentException;

final class NormalizeRedirectStatus
{
    private const array VALID_STATUSES = [301, 302, 303, 307, 308];

    public function __invoke(int $status) : int
    {
        if (! in_array(needle: $status, haystack: self::VALID_STATUSES, strict: true)) {
            throw new InvalidArgumentException(message: "Invalid redirect status [{$status}].");
        }

        return $status;
    }
}
