<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Dx\System\Configuration;

interface DxConfigurationInterface
{
    public function isVerbose(): bool;

    public function isColorEnabled(): bool;

    public function getDefaultTemplate(): string;
}
