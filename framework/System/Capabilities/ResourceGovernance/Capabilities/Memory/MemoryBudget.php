<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ResourceGovernance\Capabilities\Memory;

final class MemoryBudget
{
    public function __construct(
        public int $workerLimit,
        public int $requestLimit,
    ) {
    }

    public static function fromString(string $worker, string $request = '32M'): self
    {
        return new self(
            workerLimit: self::parseSize($worker),
            requestLimit: self::parseSize($request),
        );
    }

    private static function parseSize(string $size): int
    {
        $unit = strtoupper(substr($size, -1));
        $value = (int) substr($size, 0, -1);

        return match ($unit) {
            'K' => $value * 1024,
            'M' => $value * 1024 * 1024,
            'G' => $value * 1024 * 1024 * 1024,
            default => (int) $size,
        };
    }
}
