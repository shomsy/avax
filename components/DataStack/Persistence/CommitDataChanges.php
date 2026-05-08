<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence;

final readonly class CommitDataChanges
{
    public function __construct(
        private object $databaseRuntime,
    ) {
    }
}
