<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Configuration;

interface ObservabilityConfigurationInterface
{
    public function isEnabled() : bool;

    public function getDriver() : string;

    public function shouldSample() : bool;

    public function shouldRedact() : bool;
}
