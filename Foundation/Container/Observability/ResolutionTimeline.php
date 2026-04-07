<?php

declare(strict_types=1);

namespace Avax\Container\Observability;

use Avax\Container\Foundation\Time\Clock;

final class ResolutionTimeline
{
    /** @var list<array{time: float, action: string, serviceId: string, outcome: string}> */
    private array $entries = [];

    public function __construct(
        private readonly Clock $clock = new Clock
    ) {}

    public function record(string $action, string $serviceId, string $outcome) : void
    {
        $this->entries[] = [
            'time'     => $this->clock->now(),
            'action'   => $action,
            'serviceId'=> $serviceId,
            'outcome'  => $outcome,
        ];
    }

    public function all() : array
    {
        return $this->entries;
    }

    public function reset() : void
    {
        $this->entries = [];
    }
}
