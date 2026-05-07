<?php

declare(strict_types=1);

namespace Avax\Components\Operations\BackgroundProcesses\System\PublicSurface;

use Avax\Components\Operations\BackgroundProcesses\System\Capabilities\HealthPolicy\HealthPolicy;
use Avax\Components\Operations\BackgroundProcesses\System\Capabilities\ProcessRegistry\ProcessRegistry;
use Avax\Components\Operations\BackgroundProcesses\System\Capabilities\RestartPolicy\RestartPolicy;
use Avax\Components\Operations\BackgroundProcesses\System\Capabilities\SupervisionPolicy\SupervisionPolicy;
use Avax\Components\Operations\BackgroundProcesses\System\Configuration\BackgroundProcessesConfiguration;
use Avax\Components\Operations\BackgroundProcesses\System\Flows\MonitorBackgroundProcess\MonitorBackgroundProcess;
use Avax\Components\Operations\BackgroundProcesses\System\Flows\RestartBackgroundProcess\RestartBackgroundProcess;
use Avax\Components\Operations\BackgroundProcesses\System\Flows\StartBackgroundProcess\StartBackgroundProcess;
use Avax\Components\Operations\BackgroundProcesses\System\Flows\StopBackgroundProcess\StopBackgroundProcess;

final class BackgroundProcesses
{
    public static function registry() : ProcessRegistry
    {
        return new ProcessRegistry();
    }

    public static function supervisionPolicy() : SupervisionPolicy
    {
        return new SupervisionPolicy();
    }

    public static function restartPolicy(int $maxRestarts = 5, int $windowSeconds = 60) : RestartPolicy
    {
        return new RestartPolicy(maxRestarts: $maxRestarts, windowSeconds: $windowSeconds);
    }

    public static function healthPolicy(int $checkIntervalSeconds = 30) : HealthPolicy
    {
        return new HealthPolicy(checkIntervalSeconds: $checkIntervalSeconds);
    }

    /**
     * @return array{id:string,name:string,pid:int|null,status:string,started:bool}
     */
    public static function start(ProcessRegistry $registry, string $id, string $name, callable $command) : array
    {
        return (new StartBackgroundProcess())->execute(registry: $registry, id: $id, name: $name, command: $command);
    }

    /**
     * @return array{id:string,stopped:bool,reason?:string}
     */
    public static function stop(ProcessRegistry $registry, string $id) : array
    {
        return (new StopBackgroundProcess())->execute(registry: $registry, id: $id);
    }

    /**
     * @return array{id:string,restarted:bool,reason?:string,restart_count?:int,delay_seconds?:int}
     */
    public static function restart(ProcessRegistry $registry, string $id, callable $command, int $exitCode = 0) : array
    {
        return (new RestartBackgroundProcess(
            registry     : $registry,
            supervision  : new SupervisionPolicy(),
            restartPolicy: new RestartPolicy(),
        ))->execute(id: $id, command: $command, exitCode: $exitCode);
    }

    /**
     * @return array{healthy:int,unhealthy:int,total:int,processes:list<array{id:string,name:string,healthy:bool}>}
     */
    public static function monitor(ProcessRegistry $registry) : array
    {
        return (new MonitorBackgroundProcess())->execute(registry: $registry);
    }
}
