<?php

declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes;

use Attribute;
use InvalidArgumentException;

/**
 * Integer Validation Rule - validates integer values only
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class IntegerRule
{
    public function __construct(
        private int|null $min = null,
        private int|null $max = null,
        private string   $message = 'Field "{property}" must be a valid integer.',
    ) {}

    public function validate(mixed $value, string $property) : void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_int($value)) {
            throw new InvalidArgumentException(
                str_replace('{property}', $property, $this->message)
            );
        }

        if ($this->min !== null && $value < $this->min) {
            throw new InvalidArgumentException(
                "Field \"{$property}\" must be at least {$this->min}."
            );
        }

        if ($this->max !== null && $value > $this->max) {
            throw new InvalidArgumentException(
                "Field \"{$property}\" must be at most {$this->max}."
            );
        }
    }
}