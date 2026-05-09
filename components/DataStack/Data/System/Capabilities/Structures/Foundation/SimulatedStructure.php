<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation;

interface SimulatedStructure extends DataStructure
{
    public function simulatedConcept() : string;

    public function simulationBoundary() : string;
}
