<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading;

use Attribute;
use InvalidArgumentException;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class RegexPattern
{
    public function __construct(public string $pattern) {}

    public function validate(mixed $value, string $field) : void
    {
        if (! preg_match($this->pattern, (string) $value)) {
            throw new InvalidArgumentException(
                sprintf('Field "%s" does not match the required pattern.', $field),
            );
        }
    }
}
