<?php

declare(strict_types=1);

namespace Avax\Components\ResourceGovernor\System\Capabilities\Memory;

final readonly class MemoryBudget
{
    public function __construct(
        public int $workerLimit,
        public int $requestLimit,
    ) {}

    public static function fromString(string $worker, string $request) : self
    {
        return new self(
            workerLimit : self::toBytes($worker),
            requestLimit: self::toBytes($request),
        );
    }

    private static function toBytes(string $memory) : int
    {
        $val  = trim($memory);
        $last = strtolower($val[strlen($val) - 1]);
        $val  = (int) $val;
        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }
}
