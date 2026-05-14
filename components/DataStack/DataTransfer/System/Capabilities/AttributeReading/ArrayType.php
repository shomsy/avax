<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading;

use Attribute;
use InvalidArgumentException;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class ArrayType
{
    /**
 * @throws InvalidArgumentException
 */
public function validate(mixed $value, string $field) : void
    {
        if (! is_array($value)) {
            throw new InvalidArgumentException(
                sprintf('Field "%s" must be an array.', $field),
            );
        }
    }
}
