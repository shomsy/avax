<?php

declare(strict_types=1);

namespace Avax\Components\Operations\BackgroundProcesses\System\Flows\RestartBackgroundProcess;

use Avax\Components\Operations\BackgroundProcesses\System\Capabilities\ProcessRegistry\ProcessRegistry;
use Avax\Components\Operations\BackgroundProcesses\System\Capabilities\RestartPolicy\RestartPolicy;
use Avax\Components\Operations\BackgroundProcesses\System\Capabilities\SupervisionPolicy\SupervisionPolicy;

final class RestartBackgroundProcess
{
    public function __construct(
        private readonly ProcessRegistry   $registry,
        private readonly SupervisionPolicy $supervision = new SupervisionPolicy(),
        private readonly RestartPolicy     $restartPolicy = new RestartPolicy(),
    ) {}

    /**
     * @return array{id:string,restarted:bool,reason?:string,restart_count?:int,delay_seconds?:int}
     */
    public function execute(string $id, callable $command, int $exitCode = 0) : array
    {
        $process = $this->registry->get(id: $id);

        if ($process === null) {
            return [
                'id'        => $id,
                'restarted' => false,
                'reason'    => 'Process not found',
            ];
        }

        $shouldRestart = $this->supervision->shouldRestart(
            strategy    : 'always',
            exitCode    : $exitCode,
            restartCount: $process['restart_count'],
            maxRestarts : 5,
        );

        if (! $shouldRestart) {
            return [
                'id'        => $id,
                'restarted' => false,
                'reason'    => 'Restart limit exceeded or policy forbids restart',
            ];
        }

        $this->registry->incrementRestarts(id: $id);
        $delay = $this->restartPolicy->delaySeconds(restartCount: $process['restart_count']);

        return [
            'id'            => $id,
            'restarted'     => true,
            'restart_count' => $process['restart_count'] + 1,
            'delay_seconds' => $delay,
        ];
    }
}
