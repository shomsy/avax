<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Capabilities\SchemaValidation;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\SystemDesign\System\Foundation\Failure\SchemaParseException;

/**
 * Native PHP YAML parser for V3 schema files.
 *
 * Supports the subset of YAML used by AvaX V3 schemas:
 * - Key-value pairs
 * - Nested maps
 * - Lists (dash-prefixed and inline)
 * - Scalars: string, int, float, bool, null
 *
 * Does NOT support:
 * - Multi-document files
 * - Anchors and aliases
 * - Complex flow sequences
 *
 * @experimental V3 labs
 */
final class NativeYamlParser
{
    public function __construct(
        private readonly ?Filesystem $filesystem = null,
    ) {}

    /**
     * Parse a YAML file from disk.
     *
     * @param string $path File path.
     *
     * @return array<int|string, mixed>
     * @throws SchemaParseException
     */
    public function parseFile(string $path) : array
    {
        $fs = $this->filesystem ?? new Filesystem();

        if (! $fs->isReadable($path)) {
            throw new SchemaParseException("Cannot read YAML file: {$path}");
        }

        $content = $fs->read($path);

        return $this->parse($content);
    }

    /**
     * Parse a YAML string into a PHP array.
     *
     * @param string $input YAML content.
     *
     * @return array<int|string, mixed>
     * @throws SchemaParseException
     */
    public function parse(string $input) : array
    {
        $lines = explode("\n", $input);
        $lines = $this->stripCommentsAndBlanks($lines);

        return $this->parseLines($lines, 0, 0)['value'];
    }

    /**
     * @param list<string> $lines
     *
     * @return list<string>
     */
    private function stripCommentsAndBlanks(array $lines) : array
    {
        $result = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }
            $result[] = $line;
        }

        return $result;
    }

    /**
     * @param list<string> $lines
     *
     * @return array{value: array<int|string, mixed>, nextIndex: int}
     */
    private function parseLines(array $lines, int $start, int $baseIndent) : array
    {
        $result = [];
        $i      = $start;

        while ( $i < count($lines) ) {
            $line   = $lines[$i];
            $indent = $this->getIndent($line);

            if ($indent < $baseIndent) {
                break;
            }

            if ($indent > $baseIndent && $result === []) {
                // Nested content without a parent key — skip
                $i++;
                continue;
            }

            $trimmed = trim($line);

            // List item
            if (str_starts_with($trimmed, '- ')) {
                // Switch to list parsing
                [$list, $nextIndex] = $this->parseList($lines, $i, $indent);

                return ['value' => $list, 'nextIndex' => $nextIndex];
            }

            // Key: value
            $colonPos = $this->findKeyColon($trimmed);
            if ($colonPos === null) {
                $i++;
                continue;
            }

            $key  = trim(substr($trimmed, 0, $colonPos));
            $rest = trim(substr($trimmed, $colonPos + 1));

            if ($rest !== '' && $rest !== '|' && $rest !== '>') {
                // Inline value
                $result[$key] = $this->parseScalar($rest);
                $i++;
            } else {
                // Check next line for nested content
                $nextLine   = $lines[$i + 1] ?? '';
                $nextIndent = $this->getIndent($nextLine);

                if ($nextIndent > $indent && ! str_starts_with(trim($nextLine), '- ')) {
                    // Nested map
                    $parsed       = $this->parseLines($lines, $i + 1, $nextIndent);
                    $result[$key] = $parsed['value'];
                    $i            = $parsed['nextIndex'];
                } elseif ($nextIndent > $indent && str_starts_with(trim($nextLine), '- ')) {
                    // Nested list
                    [$list, $nextIndex] = $this->parseList($lines, $i + 1, $nextIndent);
                    $result[$key] = $list;
                    $i            = $nextIndex;
                } else {
                    // Empty value
                    $result[$key] = null;
                    $i++;
                }
            }
        }

        return ['value' => $result, 'nextIndex' => $i];
    }

    private function getIndent(string $line) : int
    {
        return strlen($line) - strlen(ltrim($line));
    }

    /**
     * @param list<string> $lines
     *
     * @return array{0: list<mixed>, 1: int}
     */
    private function parseList(array $lines, int $start, int $baseIndent) : array
    {
        $result = [];
        $i      = $start;

        while ( $i < count($lines) ) {
            $line   = $lines[$i];
            $indent = $this->getIndent($line);

            if ($indent < $baseIndent) {
                break;
            }

            $trimmed = trim($line);

            if (! str_starts_with($trimmed, '- ')) {
                if ($indent <= $baseIndent && $result !== []) {
                    break;
                }
                $i++;
                continue;
            }

            $itemContent = trim(substr($trimmed, 2));

            // Check if item is a key: value (map item)
            $colonPos = $this->findKeyColon($itemContent);
            if ($colonPos !== null) {
                // Parse as a map entry, collect sibling entries at deeper indent
                $key     = trim(substr($itemContent, 0, $colonPos));
                $rest    = trim(substr($itemContent, $colonPos + 1));
                $itemMap = [];

                if ($rest !== '') {
                    $itemMap[$key] = $this->parseScalar($rest);
                } else {
                    // Check for nested content
                    $nextLine   = $lines[$i + 1] ?? '';
                    $nextIndent = $this->getIndent($nextLine);
                    $itemIndent = $indent + 2; // After "- "

                    if ($nextIndent > $itemIndent) {
                        $parsed        = $this->parseLines($lines, $i + 1, $nextIndent);
                        $itemMap[$key] = $parsed['value'];
                        $i             = $parsed['nextIndex'];
                        $result[]      = $itemMap;
                        continue;
                    }

                    $itemMap[$key] = null;
                }

                // Look for more key: value pairs at deeper indent (same list item)
                $i++;
                $itemBaseIndent = $indent + 2;
                while ( $i < count($lines) ) {
                    $nextLine    = $lines[$i];
                    $nextIndent  = $this->getIndent($nextLine);
                    $nextTrimmed = trim($nextLine);

                    if ($nextIndent < $itemBaseIndent || str_starts_with($nextTrimmed, '- ')) {
                        break;
                    }

                    $subColonPos = $this->findKeyColon($nextTrimmed);
                    if ($subColonPos !== null) {
                        $subKey  = trim(substr($nextTrimmed, 0, $subColonPos));
                        $subRest = trim(substr($nextTrimmed, $subColonPos + 1));

                        if ($subRest !== '') {
                            $itemMap[$subKey] = $this->parseScalar($subRest);
                            $i++;
                        } else {
                            $deepNext   = $lines[$i + 1] ?? '';
                            $deepIndent = $this->getIndent($deepNext);
                            if ($deepIndent > $nextIndent) {
                                $parsed           = $this->parseLines($lines, $i + 1, $deepIndent);
                                $itemMap[$subKey] = $parsed['value'];
                                $i                = $parsed['nextIndex'];
                            } else {
                                $itemMap[$subKey] = null;
                                $i++;
                            }
                        }
                    } else {
                        $i++;
                    }
                }

                $result[] = $itemMap;
            } else {
                // Simple scalar list item
                $result[] = $this->parseScalar($itemContent);
                $i++;
            }
        }

        return [$result, $i];
    }

    /**
     * Find the position of the key-separator colon (not inside a string).
     */
    private function findKeyColon(string $line) : ?int
    {
        $pos = strpos($line, ':');
        if ($pos === false) {
            return null;
        }

        // Ensure it's followed by space, end of string, or is the end
        $next = $line[$pos + 1] ?? '';
        if ($next === '' || $next === ' ') {
            return $pos;
        }

        return null;
    }

    /**
     * Parse a scalar YAML value into a PHP value.
     */
    private function parseScalar(string $value) : mixed
    {
        // Inline array [...]
        if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
            $inner = trim(substr($value, 1, -1));
            if ($inner === '') {
                return [];
            }

            return array_map(
                fn (string $part) => $this->parseScalar(trim($part)),
                explode(',', $inner),
            );
        }

        // Quoted string
        if ((str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            return substr($value, 1, -1);
        }

        // Boolean
        $lower = strtolower($value);
        if (in_array($lower, ['true', 'yes', 'on'], true)) {
            return true;
        }
        if (in_array($lower, ['false', 'no', 'off'], true)) {
            return false;
        }

        // Null
        if (in_array($lower, ['null', '~', ''], true)) {
            return null;
        }

        // Integer
        if (preg_match('/^-?\d+$/', $value) === 1) {
            return (int) $value;
        }

        // Float
        if (preg_match('/^-?\d+\.\d+$/', $value) === 1) {
            return (float) $value;
        }

        // String — strip inline comments
        $commentPos = strpos($value, ' #');
        if ($commentPos !== false) {
            $value = trim(substr($value, 0, $commentPos));
        }

        return $value;
    }
}
