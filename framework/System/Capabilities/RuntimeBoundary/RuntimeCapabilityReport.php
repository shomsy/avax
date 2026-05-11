<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RuntimeBoundary;

final readonly class RuntimeCapabilityReport
{
    public function __construct(
        /** @var list<RuntimeAdapter> $adapters */
        private array $adapters,
    ) {}

    /**
     * @return array<string, array<string, mixed>>
     */
    public function report() : array
    {
        $report = [];

        foreach ($this->adapters as $adapter) {
            $report[$adapter->name()] = [
                'available'    => $adapter->isAvailable(),
                'capabilities' => $adapter->capabilities(),
            ];
        }

        return $report;
    }
}
