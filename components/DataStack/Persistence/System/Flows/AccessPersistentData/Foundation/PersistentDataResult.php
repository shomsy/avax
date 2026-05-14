<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Flows\AccessPersistentData\Foundation;

final class PersistentDataResult
{
    public function __construct(
        public array $rows,
        public int $affectedRows = 0
    ) {
    }
}
