<?php

declare(strict_types=1);

namespace Avax\Components\DumpDebugger\System\PublicSurface;

use Avax\Components\View\System\Capabilities\Engines\BladeTemplateEngine;
use JetBrains\PhpStorm\NoReturn;
use Throwable;

/**
 * DumpDebugger Public Surface.
 *
 * Provides interactive variable dumping for debugging purposes.
 * Leverages the Blade template engine to render a styled output.
 */
final class Dump
{
    #[NoReturn]
    public static function ddx(mixed ...$args) : never
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1] ?? [];
        $html  = self::renderDump($args, $trace['file'] ?? 'unknown', $trace['line'] ?? 0);

        if (! headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
        }

        echo $html;
        exit(1);
    }

    private static function renderDump(array $args, string $file, int $line) : string
    {
        $viewsPath = __DIR__ . '/../../views';
        if (! is_dir($viewsPath)) {
            $viewsPath = sys_get_temp_dir();
        }

        $blade = new BladeTemplateEngine($viewsPath, sys_get_temp_dir());

        try {
            return $blade->renderView('dump', [
                'args' => $args,
                'file' => $file,
                'line' => $line,
            ]);
        } catch (Throwable) {
            // Fallback if view fails
            ob_start();
            var_dump($args);

            return ob_get_clean() ?: '';
        }
    }

    public static function dumpx(mixed ...$args) : void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1] ?? [];
        echo self::renderDump($args, $trace['file'] ?? 'unknown', $trace['line'] ?? 0);
    }
}
