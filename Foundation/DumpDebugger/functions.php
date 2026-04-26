<?php

declare(strict_types=1);

use Avax\DumpDebugger\DumpDebugger;
use JetBrains\PhpStorm\NoReturn;

// dd() like function
if (! function_exists(function: 'ddx')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param mixed ...$args
     *
     * @return never
     */
    #[NoReturn]
    function ddx(mixed ...$args) : never
    {
        DumpDebugger::ddx(...$args);
    }
}

// dump() like function
if (! function_exists(function: 'dumpx')) {
    /**
     * Dump the passed variables without ending the script.
     *
     * @param mixed ...$args
     *
     * @return void
     */
    function dumpx(mixed ...$args) : void
    {
        DumpDebugger::dumpx(...$args);
    }
}
