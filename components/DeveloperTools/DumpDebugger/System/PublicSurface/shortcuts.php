<?php

declare(strict_types=1);

/**
 * DumpDebugger shortcuts for global access.
 */

use Symfony\Component\VarDumper\VarDumper;

if (! function_exists('dd')) {
    /**
     * Dump variables and die.
     */
    function dd(mixed ...$vars): never
    {
        foreach ($vars as $var) {
            dump($var);
        }
        exit(1);
    }
}

if (! function_exists('dump')) {
    /**
     * Dump variables without dying.
     */
    function dump(mixed ...$vars): void
    {
        foreach ($vars as $var) {
            if (class_exists(VarDumper::class)) {
                VarDumper::dump($var);
            } else {
                var_dump($var);
            }
        }
    }
}

if (! function_exists('d')) {
    /**
     * Dump variables with label and die.
     */
    function d(mixed ...$vars): never
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $file = basename($backtrace[0]['file'] ?? 'unknown');
        $line = $backtrace[0]['line'] ?? '?';

        echo "<pre style='background:#1e1e1e;color:#d4d4d4;padding:12px;font-size:13px;'>\n";
        echo "<strong style='color:#569cd6;'>{$file}:{$line}</strong>\n";
        echo str_repeat('-', 60) . "\n";

        foreach ($vars as $key => $var) {
            echo "\n[{$key}] ";
            if (class_exists(VarDumper::class)) {
                VarDumper::dump($var);
            } else {
                var_dump($var);
            }
        }

        echo '</pre>';
        exit(1);
    }
}
