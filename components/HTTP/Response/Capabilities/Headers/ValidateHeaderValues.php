<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Capabilities\Headers;

use InvalidArgumentException;

final class ValidateHeaderValues
{
    /**
     * @param list<string> $values
     *
     * @return list<string>
     */
    public function __invoke(array $values) : array
    {
        foreach ($values as $value) {
            if (preg_match(pattern: "/[\r\n]/", subject: $value) === 1) {
                throw new InvalidArgumentException(message: 'HTTP header values cannot contain CRLF characters.');
            }
        }

        return $values;
    }
}
