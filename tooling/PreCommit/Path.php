<?php

declare(strict_types=1);

namespace Avax\Tooling\PreCommit;
final class Path
{
    public static function normalize(string $path): string
    {
        $realPath = realpath($path);

        if ($realPath !== false) {
            return rtrim($realPath, DIRECTORY_SEPARATOR);
        }

        return rtrim($path, DIRECTORY_SEPARATOR);
    }

    public static function join(string ...$parts): string
    {
        $cleanParts = [];

        foreach ($parts as $index => $part) {
            $part = $index === 0
                ? rtrim($part, DIRECTORY_SEPARATOR)
                : trim($part, DIRECTORY_SEPARATOR);

            if ($part !== '') {
                $cleanParts[] = $part;
            }
        }

        return implode(DIRECTORY_SEPARATOR, $cleanParts);
    }

    public static function isAbsolute(string $path): bool
    {
        return str_starts_with($path, DIRECTORY_SEPARATOR)
            || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1;
    }
}
