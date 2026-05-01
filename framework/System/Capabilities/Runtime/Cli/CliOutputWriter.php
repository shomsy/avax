<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\Cli;

use Avax\Framework\System\Capabilities\Runtime\RuntimeResult;

final readonly class CliOutputWriter
{
    public function render(RuntimeResult $result): string
    {
        return $result->output() === '' ? '' : $result->output() . PHP_EOL;
    }
}
