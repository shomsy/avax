<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\Capabilities\RunThroughProcessPool;

use Symfony\Component\Process\Process;

final readonly class StartWorkerProcess
{
    private const PHP_BINARY = 'php';

    /**
     * @param array{action: string, payload: string} $serializedWork
     *
     * @return Process
     */
    public function start(array $serializedWork, ?string $workerScript = null) : Process
    {
        $script  = $workerScript ?? $this->getDefaultWorkerScript();
        $payload = base64_encode(json_encode($serializedWork, JSON_THROW_ON_ERROR));

        $process = new Process([
                                   self::PHP_BINARY,
                                   $script,
                                   '--payload',
                                   $payload,
                               ]);

        $process->setTimeout(300.0);
        $process->start();

        return $process;
    }

    private function getDefaultWorkerScript() : string
    {
        return dirname(__DIR__, 4) . '/bin/avax';
    }

    public function getPhpBinary() : string
    {
        return self::PHP_BINARY;
    }
}
