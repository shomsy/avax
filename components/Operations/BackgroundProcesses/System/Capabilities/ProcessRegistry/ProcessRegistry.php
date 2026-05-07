<?php

declare(strict_types=1);

namespace Avax\Components\Operations\BackgroundProcesses\System\Capabilities\ProcessRegistry;

use DateTime;

final class ProcessRegistry
{
    /** @var array<string, array{id:string,name:string,status:string,pid:int|null,started_at:string,stopped_at:string|null,restart_count:int}> */
    private array $processes = [];

    public function register(string $id, string $name) : self
    {
        $this->processes[$id] = [
            'id'            => $id,
            'name'          => $name,
            'status'        => 'stopped',
            'pid'           => null,
            'started_at'    => '',
            'stopped_at'    => null,
            'restart_count' => 0,
        ];

        return $this;
    }

    public function markRunning(string $id, int $pid) : void
    {
        if (isset($this->processes[$id])) {
            $this->processes[$id]['status']     = 'running';
            $this->processes[$id]['pid']        = $pid;
            $this->processes[$id]['started_at'] = (new DateTime())->format('Y-m-d H:i:s');
        }
    }

    public function markStopped(string $id) : void
    {
        if (isset($this->processes[$id])) {
            $this->processes[$id]['status']     = 'stopped';
            $this->processes[$id]['pid']        = null;
            $this->processes[$id]['stopped_at'] = (new DateTime())->format('Y-m-d H:i:s');
        }
    }

    public function incrementRestarts(string $id) : void
    {
        if (isset($this->processes[$id])) {
            $this->processes[$id]['restart_count']++;
        }
    }

    /**
     * @return array<string, array{id:string,name:string,status:string,pid:int|null,started_at:string,stopped_at:string|null,restart_count:int}>
     */
    public function all() : array
    {
        return $this->processes;
    }

    /**
     * @return array{id:string,name:string,status:string,pid:int|null,started_at:string,stopped_at:string|null,restart_count:int}|null
     */
    public function get(string $id) : array|null
    {
        return $this->processes[$id] ?? null;
    }

    /**
     * @return list<array{id:string,name:string,status:string,pid:int|null,started_at:string,stopped_at:string|null,restart_count:int}>
     */
    public function findByStatus(string $status) : array
    {
        return array_values(array_filter(
                                array   : $this->processes,
                                callback: static fn (array $process) : bool => $process['status'] === $status
                            ));
    }
}
