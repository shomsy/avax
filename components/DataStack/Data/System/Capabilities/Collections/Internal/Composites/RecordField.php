<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Internal\Composites;

use Avax\Components\DataStack\Data\Exceptions\InvalidValueException;

/**
 * One named field inside a record.
 */
final readonly class RecordField
{
    public function __construct(
        private string $name,
        private mixed $value,
    )
    {
        if (trim($name) === '') {
            throw InvalidValueException::because(message: 'Record field name cannot be empty.');
        }
    }

    public function name() : string
    {
        return $this->name;
    }

    public function value() : mixed
    {
        return $this->value;
    }
}
