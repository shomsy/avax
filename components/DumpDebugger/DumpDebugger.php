<?php

declare(strict_types=1);

namespace Avax\DumpDebugger;

use Avax\View\BladeTemplateEngine;
use JetBrains\PhpStorm\NoReturn;

/**
 * DumpDebugger provides interactive variable dumping for debugging purposes.
 * It leverages the Blade template engine to render a styled output.
 */
class DumpDebugger
{
    /**
     * Terminates the script and renders an interactive dump.
     *
     * @param mixed ...$args
     *
     * @return never
     */
    #[NoReturn]
    public static function ddx(mixed ...$args) : never
    {
        $trace = debug_backtrace(options: DEBUG_BACKTRACE_IGNORE_ARGS, limit: 2)[1] ?? [];
        $html  = self::renderDump(args: $args, file: $trace['file'] ?? 'unknown', line: $trace['line'] ?? 0);

        if (! headers_sent()) {
            header(header: 'Content-Type: text/html; charset=utf-8');
        }

        echo $html;
        exit(1);
    }

    /**
     * Renders the Blade HTML with variables.
     *
     * @param array  $args
     * @param string $file
     * @param int    $line
     *
     * @return string
     */
    private static function renderDump(array $args, string $file, int $line) : string
    {
        // Use AvaxDump views if they exist, or fallback to temp dir for cache
        $viewsPath = __DIR__ . '/../AvaxDump/views';
        if (! is_dir($viewsPath)) {
            $viewsPath = __DIR__ . '/views';
        }

        $blade = new BladeTemplateEngine(viewsPath: $viewsPath, cachePath: sys_get_temp_dir());

        return $blade->toHtml(view: 'dump', data: [
            'args' => $args,
            'file' => $file,
            'line' => $line,
        ]);
    }

    /**
     * Outputs a styled interactive dump, without terminating the script.
     *
     * @param mixed ...$args
     *
     * @return void
     */
    public static function dumpx(mixed ...$args) : void
    {
        $trace = debug_backtrace(options: DEBUG_BACKTRACE_IGNORE_ARGS, limit: 2)[1] ?? [];
        echo self::renderDump(args: $args, file: $trace['file'] ?? 'unknown', line: $trace['line'] ?? 0);
    }
}
