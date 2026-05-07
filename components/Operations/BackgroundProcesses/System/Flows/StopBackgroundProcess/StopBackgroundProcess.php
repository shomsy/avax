<?php

declare(strict_types=1);

namespace Avax\Components\Operations\BackgroundProcesses\System\Flows\StopBackgroundProcess;

use Avax\Components\Operations\BackgroundProcesses\System\Capabilities\ProcessRegistry\ProcessRegistry;
use function function_exists;
use function posix_kill;

final readonly class StopBackgroundProcess
{
    /**
     * @return array{id:string,stopped:bool,reason?:string}
     */
    public function execute(ProcessRegistry $registry, string $id) : array
    {
        $process = $registry->get(id: $id);

        if ($process === null) {
            return [
                'id'      => $id,
                'stopped' => false,
                'reason'  => 'Process not found',
            ];
        }

        if ($process['pid'] !== null && $process['pid'] > 0 && function_exists('posix_kill')) {
            posix_kill($process['pid'], SIGTERM);
        }

        $registry->markStopped(id: $id);

        return [
            'id'      => $id,
            'stopped' => true,
        ];
    }
}
