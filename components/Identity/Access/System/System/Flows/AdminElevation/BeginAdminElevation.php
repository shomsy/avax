<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\System\Flows\AdminElevation;

/**
 * BeginAdminElevation - Flow to initiate temporary admin privilege elevation.
 */
final class BeginAdminElevation
{
    private static bool $elevated = false;

    public static function active() : bool
    {
        return self::$elevated;
    }

    public static function reset() : void
    {
        self::$elevated = false;
    }

    public function execute() : void
    {
        self::$elevated = true;
    }
}
