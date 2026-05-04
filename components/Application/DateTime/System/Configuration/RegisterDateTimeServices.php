<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\Configuration;

use Avax\Components\Application\DateTime\System\Capabilities\Timezone\Timezone;
use Avax\Components\Application\DateTime\System\PublicSurface\Clock;

/**
 * Configuration unit to assemble DateTime component services.
 */
final class RegisterDateTimeServices
{
    public function build(): Clock
    {
        return new Clock(
            timezone: new Timezone('UTC')
        );
    }
}