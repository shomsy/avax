<?php

declare(strict_types=1);

namespace Avax\Components\Operations\BackgroundProcesses\System\Capabilities\HealthPolicy;

final class HealthPolicy
{
    public function __construct(
        private readonly int $checkIntervalSeconds = 30,
    ) {}

    /**
     * @param array<string, array{id:string,name:string,status:string,pid:int|null,started_at:string,stopped_at:string|null,restart_count:int}> $processes
     *
     * @return array{healthy:int,unhealthy:int,total:int,processes:list<array{id:string,name:string,healthy:bool}>}
     */
    public function checkAll(array $processes) : array
    {
        $healthy   = 0;
        $unhealthy = 0;
        $results   = [];

        foreach ($processes as $process) {
            $isHealthy = $this->isHealthy(process: $process);
            if ($isHealthy) {
                $healthy++;
            } else {
                $unhealthy++;
            }

            $results[] = [
                'id'      => $process['id'],
                'name'    => $process['name'],
                'healthy' => $isHealthy,
            ];
        }

        return [
            'healthy'   => $healthy,
            'unhealthy' => $unhealthy,
            'total'     => count(value: $processes),
            'processes' => $results,
        ];
    }

    /**
     * @param array<string, mixed> $process
     */
    public function isHealthy(array $process) : bool
    {
        if ($process['status'] !== 'running') {
            return false;
        }

        if ($process['pid'] === null) {
            return false;
        }

        return true;
    }
}
