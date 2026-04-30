<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Internal\Values\Option;

use Override;
use RuntimeException;

/**
 * Represents an absent value in an Option.
 */
final class None extends Option
{
    private static self|null $instance = null;

    private function __construct()
    {
        // Empty - singleton
    }

    public static function instance() : self
    {
        return self::$instance ??= new self();
    }

    #[Override]
    public function isSome() : bool
    {
        return false;
    }

    #[Override]
    public function unwrap() : mixed
    {
        throw new RuntimeException('Cannot unwrap none value.');
    }
}
