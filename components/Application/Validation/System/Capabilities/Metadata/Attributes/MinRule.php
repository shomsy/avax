<?php

declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes;

use Attribute;
use InvalidArgumentException;

/**
 * Min Validation Rule - validates minimum value for numbers/strings
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class MinRule
{
    public function __construct(
        private int|float|string $min,
        private string           $message = 'Field "{property}" must be at least {min}.',
    ) {}

    public function validate(mixed $value, string $property) : void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (is_numeric($value)) {
            if ($value < $this->min) {
                $message = str_replace(['{property}', '{min}'], [$property, $this->min], $this->message);
                throw new InvalidArgumentException($message);
            }
        } elseif (is_string($value)) {
            if (mb_strlen($value) < $this->min) {
                $message = str_replace(['{property}', '{min}'], [$property, $this->min], $this->message);
                throw new InvalidArgumentException($message);
            }
        }
    }
}