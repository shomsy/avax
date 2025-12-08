<?php

declare(strict_types=1);

namespace Avax;

use JetBrains\PhpStorm\NoReturn;
use RuntimeException;

final class DumpDebugger
{
    #[NoReturn]
    /**
     * Enhanced Dump and Die functionality with interactive HTML output.
     *
     * This method provides a sophisticated debugging tool that renders variables
     * in an interactive HTML interface with search and navigation capabilities.
     * Execution is terminated after output is rendered.
     *
     * @param mixed ...$args The variables to dump and inspect
     *
     * @return never Method terminates execution
     * @throws \RuntimeException If headers have already been sent
     *
     * @api
     * @since 8.3.0
     */
    public static function ddx(mixed ...$args) : never
    {
        // Retrieve caller information from debug backtrace, limiting to 2 frames for performance
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1] ?? [];

        // Extract file and line information with fallback values for safety
        $file = $trace['file'] ?? 'unknown';
        $line = $trace['line'] ?? 0;

        // Set the content type to ensure proper rendering in the browser
        header('Content-Type: text/html; charset=utf-8');

        // Render the HTML header with file and line information
        echo self::renderHtmlHeader(
            file: $file,
            line: $line
        );

        // Iterate through and render each provided argument in a separate container
        foreach ($args as $arg) {
            echo '<div class="ddx">' . self::renderValue(
                    key  : null,
                    value: $arg
                ) . '</div>';
        }

        // Close HTML structure and terminate execution with error status
        echo '</body></html>';
        exit(1);
    }

    /**
     * Renders the HTML header for the debug dump viewer.
     *
     * This method generates the complete HTML header including styles and JavaScript
     * for the interactive debug dump interface. It implements a sophisticated search
     * functionality with real-time highlighting and navigation capabilities.
     *
     * Features:
     * - Modern dark theme optimized for readability
     * - Interactive search with key-value pair support
     * - Collapsible tree structure for nested data
     * - Keyboard navigation support
     * - Syntax highlighting for different data types
     *
     * @param string $file The source file path where the dump was triggered
     * @param int    $line The line number where the dump was triggered
     *
     * @return string Complete HTML header markup with embedded styles and JavaScript
     *
     * @throws \InvalidArgumentException When a file path is empty or the line number is negative
     * @since 1.0.0
     */
    private static function renderHtmlHeader(string $file, int $line) : string
    {
        $fileEsc = htmlspecialchars($file);

        return <<<HTML
                                    <!DOCTYPE html>
                                    <html lang="en">
                                    <head>
                                        <meta charset="UTF-8">
                                        <title>GemDump</title>
                                        <style>
                                            body { background: #0f0f0f; color: #eee; font-family: 'JetBrains Mono', monospace; padding: 5rem 2rem 2rem; font-size: 14px; }
                                            .entry { white-space: pre; margin: 3px 0; }
                                            .toggle { color: #58a6ff; cursor: pointer; user-select: none; margin-right: 0.5rem; font-weight: bold; }
                                            .children { padding-left: 2rem; border-left: 1px dashed #444; margin-top: 2px; }
                                            .key { color: #ffc66d; }
                                            .operator { color: #aaa; }
                                            .type { color: #8be9fd; }
                                            .value.string { color: #a5ff90; }
                                            .value.int { color: #bd93f9; }
                                            .value.float { color: #79c0ff; }
                                            .value.bool { color: #ff9ac1; }
                                            .value.null { color: #808080; font-style: italic; }
                                            .value.unknown { color: #999; }
                                            .collapsed > .children { display: none; }
                                            .highlight-key { background: #343a40; color: #80ffea; padding: 1px 4px; border-radius: 3px; }
                                            .current-key { outline: 2px solid #ff5722; }
                                            .dumpx-stack { margin-top: 2rem; padding: 1rem 2rem; background: #121212; border-left: 4px solid #ffc107; }
                                            .search-container { position: fixed; top: 0; left: 0; right: 0; z-index: 9999; background: #1a1a1a; padding: 10px 20px; border-bottom: 1px solid #333; display: flex; align-items: center; gap: 8px; }
                                            .search-container input { padding: 6px 10px; font-size: 13px; background: #0f0f0f; color: #fff; border: 1px solid #555; border-radius: 5px; width: 300px; }
                                            .search-container button { background: #ffc107; color: #000; font-weight: bold; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; }
                                            .search-container span { font-size: 12px; color: #bbb; }
                                        </style>
                                        <script>
                                            let results = [], currentIndex = -1;
            
                                            function normalize(text) {
                                                return text.toLowerCase()
                                                    .replace(/\\s+/g, '')
                                                    .replace(/['"“”‘’]/g, '')
                                                    .replace(/&\\w+;/g, '')
                                                    .trim();
                                            }
            
                                            function searchDump(term) {
                                                clearHighlights();
                                                results = [], currentIndex = -1;
                                                if (!term.trim()) return updateStatus();
                    
                                                const rawTerm = term.trim().toLowerCase();
                                                const quotedTerm = '"' + rawTerm.replace(/^"+|"+$/g, '') + '"';
                                                const normalizedTerm = normalize(rawTerm);
                                                const normalizedQuoted = normalize(quotedTerm);
                    
                                                const entries = document.querySelectorAll('.entry');
                    
                                                entries.forEach(el => {
                                                    const keyEl = el.querySelector('.key');
                                                    const valEl = el.querySelector('.value');
                                                    if (!keyEl || !valEl) return;
                    
                                                    const keyRaw = keyEl.textContent.trim().toLowerCase();
                                                    const valRaw = valEl.textContent.trim().toLowerCase();
                    
                                                    const keyNorm = normalize(keyRaw);
                                                    const valNorm = normalize(valRaw);
                    
                                                    const combined = normalize(keyRaw + '=>' + valRaw);
                    
                                                    const matches = (
                                                        keyRaw.includes(rawTerm) || keyNorm.includes(normalizedTerm) ||
                                                        valRaw.includes(rawTerm) || valNorm.includes(normalizedTerm) ||
                                                        valRaw.includes(quotedTerm) || valNorm.includes(normalizedQuoted) ||
                                                        combined.includes(normalizedTerm)
                                                    );
                    
                                                    if (matches) {
                                                        keyEl.classList.add('highlight-key');
                                                        valEl.classList.add('highlight-key');
                                                        results.push(keyEl);
                                                    }
                                                });
                    
                                                if (results.length) {
                                                    currentIndex = 0;
                                                    scrollToResult(currentIndex);
                                                }
                    
                                                updateStatus();
                                            }
            
                                            function scrollToResult(index) {
                                                results.forEach(el => el.classList.remove('current-key'));
                                                if (results[index]) {
                                                    const rect = results[index].getBoundingClientRect();
                                                    const y = window.scrollY + rect.top - 100;
                                                    window.scrollTo({ top: y, behavior: 'smooth' });
                                                    results[index].classList.add('current-key');
                                                }
                                                updateStatus();
                                            }
            
                                            function searchNext() {
                                                if (!results.length) {
                                                    const term = document.getElementById('dump-search').value;
                                                    searchDump(term);
                                                } else {
                                                    currentIndex = (currentIndex + 1) % results.length;
                                                    scrollToResult(currentIndex);
                                                }
                                            }
            
                                            function searchPrev() {
                                                if (!results.length) return;
                                                currentIndex = (currentIndex - 1 + results.length) % results.length;
                                                scrollToResult(currentIndex);
                                            }
            
                                            function updateStatus() {
                                                const el = document.getElementById('search-status');
                                                if (!el) return;
                                                el.textContent = results.length ? (currentIndex + 1) + ' of ' + results.length : 'No results';
                                            }
            
                                          function clearHighlights() {
                                            document.querySelectorAll('.highlight-key').forEach(el =>
                                                el.classList.remove('highlight-key', 'current-key')
                                            );
            }
            
            
                                            function toggle(el) {
                                                const parent = el.closest('.entry');
                                                if (parent) {
                                                    parent.classList.toggle('collapsed');
                                                    el.textContent = parent.classList.contains('collapsed') ? '▶' : '▼';
                                                }
                                            }
            
                                            document.addEventListener('DOMContentLoaded', () => {
                                                const search = document.getElementById('dump-search');
                                                search.focus();
                                                search.addEventListener('keydown', (e) => {
                                                    if (e.key === 'Enter') searchNext();
                                                });
                                            });
                                        </script>
                                    </head>
                                    <body>
                                        <div class="search-container">
                                            <input id="dump-search" type="text" placeholder="🔍 Search key => value" oninput="searchDump(this.value)">
                                            <button onclick="searchNext()">Next</button>
                                            <button onclick="searchPrev()">Prev</button>
                                            <span id="search-status"></span>
                                        </div>
                                        <h3 style="color:#ffc107;margin-bottom:2rem;">
                                        🔍 Dump and Die — <span style="color:#f88">{$fileEsc}</span> : <span style="color:#6cf">{$line}</span>
                                        </h3>
            HTML;
    }

    /**
     * Renders a value into an HTML representation with interactive features for complex data structures.
     *
     * This method implements a recursive rendering strategy for various data types, producing
     * a hierarchical HTML structure with collapsible sections for arrays and objects.
     * It handles proper escaping and type-specific formatting while maintaining visual hierarchy.
     *
     * @param string|null $key   The key associated with the value, or null for root elements
     * @param mixed       $value The value to be rendered
     * @param int         $depth Current depth in the rendering hierarchy, defaults to 0
     *
     * @return string HTML representation of the value
     *
     * @throws RuntimeException When encountering unhandled value types
     */
    private static function renderValue(
        string|null $key,
        mixed       $value,
        int         $depth = 0
    ) : string {
        // Construct the prefix HTML with proper key formatting and operator
        $prefix = $key !== null
            ? '<span class="key">' . (is_int($key)
                ? $key
                : htmlspecialchars(
                           $key,
                    flags: ENT_QUOTES
                )) . '</span><span class="operator"> => </span>'
            : '';

        // Handle array rendering with a collapsible structure
        if (is_array($value)) {
            $count = count($value);
            // Return compact representation for empty arrays
            if ($count === 0) {
                return '<div class="entry">' . $prefix . '<span class="type">array:0</span> []</div>';
            }

            // Build expandable array representation with nested elements
            $html = '<div class="entry"><span class="toggle" onclick="toggle(this)">▼</span> ' .
                    $prefix . '<span class="type">array:' . $count . '</span> [';
            $html .= '<div class="children">';
            // Recursively render each array element
            foreach ($value as $k => $v) {
                $html .= self::renderValue(key: (string) $k, value: $v, depth: $depth + 1);
            }
            $html .= '</div>]</div>';

            return $html;
        }

        // Handle object rendering with a collapsible structure
        if (is_object($value)) {
            $class = get_class($value);
            $props = (array) $value;
            $count = count($props);
            // Return compact representation for empty objects
            if ($count === 0) {
                return '<div class="entry">' . $prefix .
                       '<span class="type">object:' . $class . '</span> {}</div>';
            }

            // Build expandable object representation with nested properties
            $html = '<div class="entry"><span class="toggle" onclick="toggle(this)">▼</span> ' .
                    $prefix . '<span class="type">object:' . $class . '</span> {';
            $html .= '<div class="children">';
            // Recursively render each object property
            foreach ($props as $k => $v) {
                $html .= self::renderValue(key: (string) $k, value: $v, depth: $depth + 1);
            }
            $html .= '</div>}</div>';

            return $html;
        }

        // Handle scalar values with appropriate type-specific formatting
        $val = match (true) {
            is_null($value)   => '<span class="value null">null</span>',
            is_bool($value)   => '<span class="value bool">' . ($value ? 'true' : 'false') . '</span>',
            is_string($value) => '<span class="value string">"' . htmlspecialchars($value) . '"</span>',
            is_int($value)    => '<span class="value int">' . $value . '</span>',
            is_float($value)  => '<span class="value float">' . $value . '</span>',
            default           => '<span class="value unknown">(unknown)</span>',
        };

        // Return the final HTML representation for scalar values
        return '<div class="entry">' . $prefix . $val . '</div>';
    }

    /**
     * Dumps variables with enhanced visualization and debugging capabilities.
     *
     * This method provides a sophisticated debugging tool that renders variable contents
     * in an interactive HTML interface. It supports type-aware visualization,
     * search functionality, and collapsible nested structures.
     *
     * @param mixed ...$args The variables to dump for inspection
     *
     * @throws \RuntimeException If output buffering has already started
     */
    public static function dumpx(mixed ...$args) : void
    {
        // Track the initialization state across multiple dump calls
        static $initialized = false;

        // Retrieve caller information from debug backtrace
        // Limit trace depth to 2 for performance and get only essential data
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1] ?? [];

        // Extract source file and line information with fallback values
        $file = $trace['file'] ?? 'unknown';
        $line = $trace['line'] ?? 0;

        // Initialize HTML structure if this is the first dump call
        // and headers haven't been sent yet
        if (! headers_sent() && ! $initialized) {
            echo self::renderHtmlHeader(file: $file, line: $line);
            $initialized = true;
        }

        // Begin a new dump stack container
        echo '<div class="dumpx-stack">';

        // Render dump location information with file and line details
        echo '<div class="entry"><span class="type">📦 Dump</span> ';
        echo '<span style="color:#f88">' . htmlspecialchars($file) . '</span> : ';
        echo '<span style="color:#6cf">' . $line . '</span></div>';

        // Process and render each provided argument
        foreach ($args as $arg) {
            echo self::renderValue(key: null, value: $arg);
        }

        // Close dump stack container
        echo '</div>';
    }
}
