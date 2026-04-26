<?php

declare(strict_types=1);

namespace components\DataFoundation\Values\Option;

use components\DataFoundation\Exceptions\InvalidValueException;

/**
 * Absent option value.
 */
final readonly class None extends Option
{
    private static self|null $instance = null;

    private function __construct() {}

    public static function instance() : self
    {
        return self::$instance ??= new self();
    }

    public function isSome() : bool
    {
        return false;
    }

    public function unwrap() : mixed
    {
        throw InvalidValueException::because(message: 'Cannot unwrap a None option.');
    }
}
