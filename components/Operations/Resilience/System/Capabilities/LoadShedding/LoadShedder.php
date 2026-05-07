<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\LoadShedding;

final readonly class LoadShedder
{
    /**
     * @param list<string> $lowPriorityEndpoints
     */
    public function __construct(
        private array $lowPriorityEndpoints = [],
        public float  $threshold = 0.95,
    ) {}

    public function shouldShed(string $endpoint, float $currentLoad) : bool
    {
        if ($currentLoad < $this->threshold) {
            return false;
        }

        return in_array($endpoint, $this->lowPriorityEndpoints, true);
    }
}
