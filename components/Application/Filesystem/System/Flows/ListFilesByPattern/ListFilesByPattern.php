<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\ListFilesByPattern;

final readonly class ListFilesByPattern
{
    /**
     * @return list<string>
     */
    public function execute(string $pattern) : array
    {
        $cleanPattern = $this->sanitizePath($pattern);

        $files = glob($cleanPattern);
        if ($files === false) {
            return [];
        }

        sort($files);

        return $files;
    }

    private function sanitizePath(string $pattern) : string
    {
        return str_replace(["\0", "\n", "\r"], '', $pattern);
    }
}
