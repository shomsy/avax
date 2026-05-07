<?php

declare(strict_types=1);

namespace Avax\Components\API\Contracts\System\Capabilities\DeprecationTracker;

final class DeprecationTracker
{
    /** @var array<string, array{sunset_date:string|null,replacement:string|null,reason:string}> */
    private array $deprecatedEndpoints = [];

    public function deprecate(string $method, string $path, string $sunsetDate, string $replacement = '', string $reason = '') : self
    {
        $key                             = "{$method}:{$path}";
        $this->deprecatedEndpoints[$key] = [
            'sunset_date' => $sunsetDate,
            'replacement' => $replacement,
            'reason'      => $reason,
        ];

        return $this;
    }

    /**
     * @return array<string, array{sunset_date:string|null,replacement:string|null,reason:string}>
     */
    public function all() : array
    {
        return $this->deprecatedEndpoints;
    }

    public function isDeprecated(string $method, string $path) : bool
    {
        $key = "{$method}:{$path}";

        return isset($this->deprecatedEndpoints[$key]);
    }
}
