<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\Configuration;

final readonly class RegisterValidationRule
{
    public function register(DataTransferConfig $config, string $attributeClass, object|callable $rule) : DataTransferConfig
    {
        return $config->withValidationRule(attributeClass: $attributeClass, rule: $rule);
    }
}
