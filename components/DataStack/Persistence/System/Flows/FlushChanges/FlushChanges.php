<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Flows\FlushChanges;

use Avax\Components\DataStack\Persistence\System\Capabilities\UnitOfWork\UnitOfWork;

final class FlushChanges
{
    public function __construct(
        private readonly UnitOfWork $unitOfWork,
    ) {}

    public function flush(): void
    {
        $this->unitOfWork->flush();
    }
}
