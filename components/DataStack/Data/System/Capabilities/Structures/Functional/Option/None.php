<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Functional\Option;

use Avax\Framework\System\Capabilities\StateReset\ResettableState;
use Override;
use RuntimeException;

/**
 * Represents an absent value in an Option.
 */
final class None extends Option implements ResettableState
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

    #[Override]
    public function resetState(): void
    {
        self::$instance = null;
    }

    #[Override]
    public function isSome(): bool
    {
        return false;
    }

    #[Override]
    /**
 * @throws RuntimeException
 */
public function unwrap(): mixed
    {
        throw new RuntimeException('Cannot unwrap none value.');
    }
}
