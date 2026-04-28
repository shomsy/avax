<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\DI\Capabilities\Diagnostics\Observability;

use Avax\Components\Application\Container\DI\Foundation\Time\Clock;

/**
 * Records ordered resolution events when diagnostics require them.
 */
final class ResolutionTimeline
{
    /** @var list<array{time: float, action: string, serviceId: string, outcome: string}> */
    private array          $entries = [];
    private readonly bool  $enabled;
    private readonly Clock $clock;

    public function __construct(
        Clock|null $clock = null,
        bool       $enabled = true
    )
    {
        $clock         ??= new Clock;
        $this->clock   = $clock;
        $this->enabled = $enabled;
    }

    /**
     * Records one timeline event when diagnostics are enabled.
     */
    public function record(string $action, string $serviceId, string $outcome) : void
    {
        if (! $this->enabled) {
            return;
        }

        $this->entries[] = [
            'time'      => $this->clock->now(),
            'action'    => $action,
            'serviceId' => $serviceId,
            'outcome'   => $outcome,
        ];
    }

    /**
     * Reports whether timeline recording is enabled.
     */
    public function enabled() : bool
    {
        return $this->enabled;
    }

    /**
     * Returns the recorded timeline entries.
     */
    public function all() : array
    {
        return $this->entries;
    }

    /**
     * Clears all recorded timeline entries.
     */
    public function reset() : void
    {
        $this->entries = [];
    }
}
