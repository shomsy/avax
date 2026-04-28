<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Configuration;

use Avax\Components\DataStack\Data\System\Capabilities\Arrays\ArrayReader;
use Avax\Components\DataStack\Data\System\Capabilities\Arrays\ArrayWriter;
use Avax\Components\DataStack\Data\System\Flows\Aggregate\AverageValues;
use Avax\Components\DataStack\Data\System\Flows\Aggregate\SumValues;
use Avax\Components\DataStack\Data\System\Flows\Read\ReadNestedValue;
use Avax\Components\DataStack\Data\System\Flows\Write\WriteNestedValue;
use Avax\Components\DataStack\Data\System\PublicSurface\Data;

/**
 * Configuration unit to assemble Data component services.
 */
final class RegisterDataServices
{
    public function build() : Data
    {
        $sumValues = new SumValues();

        return new Data(
            arrayReader     : new ArrayReader(),
            arrayWriter     : new ArrayWriter(),
            readNestedValue : new ReadNestedValue(),
            writeNestedValue: new WriteNestedValue(),
            sumValues       : $sumValues,
            averageValues   : new AverageValues($sumValues)
        );
    }
}
