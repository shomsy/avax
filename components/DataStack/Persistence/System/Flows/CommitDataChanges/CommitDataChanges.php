<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Flows\CommitDataChanges;

final readonly class CommitDataChanges
{
    public function __construct(
        private object $databaseRuntime,
    ) {
    }
}
