<?php

declare(strict_types=1);

namespace Avax\Components\DateTime\System\Configuration;

use Avax\Components\DateTime\System\Capabilities\Timezone\Timezone;
use Avax\Components\DateTime\System\PublicSurface\Clock;

/**
 * Configuration unit to assemble DateTime component services.
 */
final class RegisterDateTimeServices
{
    public function build() : Clock
    {
        return new Clock(
            timezone: new Timezone('UTC')
        );
    }
}