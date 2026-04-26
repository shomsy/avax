<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Headers;

use InvalidArgumentException;
use Stringable;

final class NormalizeHeaderValues
{
    /**
     * @return list<string>
     */
    public function __invoke(mixed $value) : array
    {
        $items = is_array(value: $value) ? $value : [$value];

        $normalized = [];
        foreach ($items as $item) {
            if (is_scalar(value: $item) || $item instanceof Stringable || $item === null) {
                $normalized[] = (string) $item;
                continue;
            }

            throw new InvalidArgumentException(message: 'HTTP header values must be scalar or stringable.');
        }

        return new ValidateHeaderValues()(values: $normalized);
    }
}
