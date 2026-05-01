<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\Cli;

use Avax\Framework\System\Capabilities\Runtime\RuntimeResult;

final readonly class CliOutputWriter
{
    public function render(RuntimeResult $runtimeResult) : string
    {
        return $runtimeResult->output() === '' ? '' : $runtimeResult->output() . PHP_EOL;
    }
}
