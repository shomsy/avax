<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation;

interface ProbabilisticStructure extends DataStructure
{
    public function add(mixed $value) : static;

    public function mightContain(mixed $value) : bool;
}
