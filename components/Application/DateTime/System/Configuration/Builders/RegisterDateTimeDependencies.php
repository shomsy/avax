<?php

declare(strict_types=1);

namespace Avax\Components\Application\DateTime\System\Configuration\Builders;

use Avax\Components\Application\DateTime\System\Capabilities\Timezone\UtcTimezone;
use Avax\Components\Application\DateTime\System\PublicSurface\Clock;

/**
 * Configuration unit to assemble DateTime component services.
 */
final class RegisterDateTimeDependencies
{
    public function build(): Clock
    {
        Clock::setDefaultTimezone(new UtcTimezone());
        return new Clock();
    }
}
