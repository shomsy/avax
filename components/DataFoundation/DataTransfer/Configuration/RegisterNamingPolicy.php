<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Configuration;

use Closure;

final readonly class RegisterNamingPolicy
{
    public function register(DataTransferConfig $config, Closure $policy) : DataTransferConfig
    {
        return $config->withNamingPolicy(policy: $policy);
    }
}
