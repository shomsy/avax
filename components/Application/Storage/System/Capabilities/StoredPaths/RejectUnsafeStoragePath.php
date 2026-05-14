<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Capabilities\StoredPaths;

use Avax\Components\Application\Storage\System\Foundation\Failure\InvalidStoragePath;

final readonly class RejectUnsafeStoragePath
{
    /**
 * @throws InvalidStoragePath
 */
public function execute(string $path) : void
    {
        if (str_contains($path, "\0")) {
            throw new InvalidStoragePath($path);
        }

        if (str_contains($path, '..')) {
            $parts = explode('/', $path);
            foreach ($parts as $part) {
                if ($part === '..') {
                    throw new InvalidStoragePath($path);
                }
            }
        }
    }
}