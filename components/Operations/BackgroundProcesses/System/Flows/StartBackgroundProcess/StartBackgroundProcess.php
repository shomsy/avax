<?php

declare(strict_types=1);

namespace Avax\Components\Operations\BackgroundProcesses\System\Flows\StartBackgroundProcess;

use Avax\Components\Operations\BackgroundProcesses\System\Capabilities\ProcessRegistry\ProcessRegistry;
use RuntimeException;
use function function_exists;

final readonly class StartBackgroundProcess
{
    /**
     * @return array{id:string,name:string,pid:int|null,status:string,started:bool}
     */
    public function execute(ProcessRegistry $registry, string $id, string $name, callable $command) : array
    {
        $registry->register(id: $id, name: $name);

        $pid = null;
        if (function_exists('pcntl_fork')) {
            $pid = pcntl_fork();

            if ($pid === -1) {
                throw new RuntimeException(message: 'Failed to fork process');
            }

            if ($pid === 0) {
                $command();
                exit(status: 0)
            }
        }

        $registry->markRunning(id: $id, pid: $pid ?? 0);

        return [
            'id'      => $id,
            'name'    => $name,
            'pid'     => $pid,
            'status'  => 'running',
            'started' => true,
        ];
    }
}
