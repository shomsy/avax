<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\Cli;

use Avax\Framework\System\Capabilities\Runtime\RuntimeResult;
use Avax\Framework\System\PublicSurface\Console\ConsoleKernelInterface;

final readonly class CliRuntime
{
    public function __construct(
        private ConsoleKernelInterface $consoleKernel,
        private CliInputReader $inputReader = new CliInputReader(),
        private CliOutputWriter $outputWriter = new CliOutputWriter(),
    ) {
    }

    /**
     * @param list<string> $argv
     */
    public function run(array $argv): RuntimeResult
    {
        $input = $this->inputReader->read(argv: $argv);

        return $this->consoleKernel->run(
            commandName: $input['command'],
            arguments  : $input['arguments'],
        );
    }

    public function render(RuntimeResult $result): string
    {
        return $this->outputWriter->render(result: $result);
    }
}
