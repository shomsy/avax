<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading;

use Attribute;
use InvalidArgumentException;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class AlphaNumOrEmail
{
    /**
 * @throws InvalidArgumentException
 */
public function validate(mixed $value, string $field) : void
    {
        $isAlphaNum = ctype_alnum((string) $value);
        $isEmail    = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;

        if (! $isAlphaNum && ! $isEmail) {
            throw new InvalidArgumentException(
                sprintf('Field "%s" must be alphanumeric or a valid email address.', $field),
            );
        }
    }
}
