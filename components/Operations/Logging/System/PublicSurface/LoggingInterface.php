<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\PublicSurface;

interface LoggingInterface
{
    public function log(string $level, string $message, array $context = []) : void;
}