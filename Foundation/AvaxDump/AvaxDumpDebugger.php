<?php

declare(strict_types=1);

namespace Avax\GemDump;

use Avax\View\BladeTemplateEngine;
use JetBrains\PhpStorm\NoReturn;

class AvaxDumpDebugger
{
    /**
     * Terminates the script and renders an interactive dump.
     *
     *
     */
    #[NoReturn]
    public static function ddx(mixed ...$args) : never
    {
        $trace = debug_backtrace(options: DEBUG_BACKTRACE_IGNORE_ARGS, limit: 2)[1] ?? [];
        $html  = self::renderDump(args: $args, file: $trace['file'] ?? 'unknown', line: $trace['line'] ?? 0);

        header(header: 'Content-Type: text/html; charset=utf-8');
        echo $html;
        exit(1);
    }

    /**
     * Renders the Blade HTML with variables.
     *
     *
     */
    private static function renderDump(array $args, string $file, int $line) : string
    {
        $blade = new BladeTemplateEngine(viewsPath: __DIR__ . '/views', cachePath: sys_get_temp_dir());

        return $blade->toHtml(view: 'dump', data: [
            'args' => $args,
            'file' => $file,
            'line' => $line,
        ]);
    }

    /**
     * Outputs a styled interactive dump, without terminating.
     *
     */
    public static function dumpx(mixed ...$args) : void
    {
        $trace = debug_backtrace(options: DEBUG_BACKTRACE_IGNORE_ARGS, limit: 2)[1] ?? [];
        echo self::renderDump(args: $args, file: $trace['file'] ?? 'unknown', line: $trace['line'] ?? 0);
    }
}
