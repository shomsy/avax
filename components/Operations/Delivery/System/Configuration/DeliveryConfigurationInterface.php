<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Delivery\System\Configuration;

interface DeliveryConfigurationInterface
{
    public function isEnabled(): bool;

    public function getEnvironment(): string;

    public function getBuildOptions(): array;
}
