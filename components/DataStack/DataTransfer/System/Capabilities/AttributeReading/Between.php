<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading;

use Attribute;
use InvalidArgumentException;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class Between
{
    public function __construct(
        public int|float $min,
        public int|float $max,
    ) {}

    /**
 * @throws InvalidArgumentException
 */
public function validate(mixed $value, string $field) : void
    {
        if (is_string($value)) {
            $length = strlen($value);
            if ($length < $this->min || $length > $this->max) {
                throw new InvalidArgumentException(
                    sprintf('Field "%s" must be between %d and %d characters.', $field, $this->min, $this->max),
                );
            }
        }

        if (is_numeric($value) && ($value < $this->min || $value > $this->max)) {
            throw new InvalidArgumentException(
                sprintf('Field "%s" must be between %s and %s.', $field, $this->min, $this->max),
            );
        }

        if (is_array($value)) {
            $count = count($value);
            if ($count < $this->min || $count > $this->max) {
                throw new InvalidArgumentException(
                    sprintf('Field "%s" must have between %d and %d items.', $field, $this->min, $this->max),
                );
            }
        }
    }
}
