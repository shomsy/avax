<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation;

interface PersistentStructure extends DataStructure
{
    public function versionId() : string;
}
