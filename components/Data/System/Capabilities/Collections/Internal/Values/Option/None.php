<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Internal\Values\Option;

use RuntimeException;

/**
 * Represents an absent value in an Option.
 */
final class None extends Option
{
    private static ?self $instance = null;

    private function __construct()
    {
        // Empty - singleton
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public function isSome(): bool
    {
        return false;
    }

    public function unwrap(): mixed
    {
        throw new RuntimeException('Cannot unwrap none value.');
    }
}