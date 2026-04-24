<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\Configuration;

final readonly class RegisterValueCaster
{
    public function register(DataTransferConfig $config, string $class, object|callable|string $caster) : DataTransferConfig
    {
        return $config->withValueCaster(class: $class, caster: $caster);
    }
}
