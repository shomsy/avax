<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\Capabilities\RunThroughProcessPool;

use Symfony\Component\Process\Process;

final readonly class StartWorkerProcess
{
    private const PHP_BINARY = 'php';

    /**
     * Start a worker process with a signed callable payload.
     *
     * @param string      $signedPayload JSON-encoded signed callable payload from CallableSerialization
     * @param string|null $signingKey    Signing key for the worker to decode the payload
     */
    public function start(string $signedPayload, string|null $workerScript = null, string|null $signingKey = null) : Process
    {
        $script  = $workerScript ?? $this->getDefaultWorkerScript();
        $encoded = base64_encode($signedPayload);

        $env = [];
        if ($signingKey !== null) {
            $env['AVAX_WORKER_SIGNING_KEY'] = $signingKey;
        }

        $process = new Process([
                                   self::PHP_BINARY,
                                   $script,
                                   '--payload',
                                   $encoded,
                               ], env: $env);

        $process->setTimeout(300.0);
        $process->start();

        return $process;
    }

    private function getDefaultWorkerScript() : string
    {
        // RunThroughProcessPool/ -> Capabilities/ -> System/ -> Parallelism/ -> Operations/ -> components/ -> project root
        return dirname(__DIR__, 6) . '/bin/avax';
    }

    public function getPhpBinary() : string
    {
        return self::PHP_BINARY;
    }
}
