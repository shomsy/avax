<?php

declare(strict_types=1);

namespace Avax\Components\Application\Facade\System\Foundation;

/**
 * Interface for Facade implementations.
 */
interface FacadeInterface
{
    /**
     * Get the accessor name for this facade.
     */
    public static function getFacadeAccessor(): string;

    /**
     * Clear the resolved instance (for testing).
     */
    public static function clearResolvedInstance(): void;
}
