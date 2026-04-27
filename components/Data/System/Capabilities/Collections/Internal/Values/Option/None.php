<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Internal\Values\Option;

use RuntimeException;

/**
 * Represents an absent value in an Option.
 */
final readonly class None extends Option
{
    private static self|null $instance = null;

    public static function instance() : self
    {
        return self::$instance ??= new self();
    }

    public function isSome() : bool { return false; }

    public function unwrap() : mixed
    {
        throw new RuntimeException('Cannot unwrap None value.');
    }
}
