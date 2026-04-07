<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Observability;

final class ResolutionTimeline
{
    /** @var list<array{time: float, action: string, serviceId: string, outcome: string}> */
    private array $entries = [];

    public function record(string $action, string $serviceId, string $outcome) : void
    {
        $this->entries[] = [
            'time'     => microtime(as_float: true),
            'action'   => $action,
            'serviceId'=> $serviceId,
            'outcome'  => $outcome,
        ];
    }

    public function all() : array
    {
        return $this->entries;
    }
}
