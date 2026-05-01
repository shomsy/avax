<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\Cli;

final readonly class CliInputReader
{
    /**
     * @param list<string> $argv
     *
     * @return array{command: string, arguments: list<string>}
     */
    public function read(array $argv): array
    {
        $command   = $argv[1] ?? 'help';
        $arguments = array_slice($argv, 2);

        return [
            'command'   => $command,
            'arguments' => array_values($arguments),
        ];
    }
}
