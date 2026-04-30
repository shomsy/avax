<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

use Override;

final class EventDrivenInvalidation implements InvalidationStrategy
{
    /** @var array<string, string> */
    private array $eventMapping = [];

    public function __construct(
        array $eventRules = [],
        array $eventMapping = [],
    )
    {
        foreach ($eventRules as $event => $reason) {
            $this->eventMapping[$event] = $reason;
        }

        foreach ($eventMapping as $event => $reason) {
            $this->eventMapping[$event] = $reason;
        }
    }

    #[Override]
    public function shouldInvalidate(string $key, string $reason, array $context = []) : bool
    {
        if (isset($this->eventMapping[$reason])) {
            return true;
        }

        $event = $context['event'] ?? null;

        return $event !== null && isset($this->eventMapping[$event]);
    }

    #[Override]
    public function strategyName() : string
    {
        return 'event_driven';
    }

    public function addRule(string $event, string $invalidationReason) : self
    {
        $new                       = clone $this;
        $new->eventMapping[$event] = $invalidationReason;

        return $new;
    }
}
