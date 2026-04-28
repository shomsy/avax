<?php

declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes;

use Attribute;
use InvalidArgumentException;

/**
 * MinLength Validation Rule - validates minimum string length
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class MinLengthRule
{
    public function __construct(
        private int    $minLength,
        private string $message = 'Field "{property}" must be at least {min} characters.',
    ) {}

    public function validate(mixed $value, string $property) : void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value) && ! is_array($value)) {
            throw new InvalidArgumentException(
                "Field \"{$property}\" must be a string or array."
            );
        }

        $length = is_string($value) ? mb_strlen($value) : count($value);

        if ($length < $this->minLength) {
            $message = str_replace(['{property}', '{min}'], [$property, $this->minLength], $this->message);
            throw new InvalidArgumentException($message);
        }
    }
}