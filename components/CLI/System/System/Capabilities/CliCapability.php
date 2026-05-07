<?php

declare(strict_types=1);

namespace Avax\Components\CLI\System\System\Capabilities;

interface CliCapability
{
    public function execute(array $args) : int;
}
