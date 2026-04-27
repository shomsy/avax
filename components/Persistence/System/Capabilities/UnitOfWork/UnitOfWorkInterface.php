<?php

declare(strict_types=1);

namespace Avax\Components\Persistence\System\Capabilities\UnitOfWork;

interface UnitOfWorkInterface
{
    public function persist(object $entity): void;

    public function remove(object $entity): void;

    public function flush(): void;

    public function clear(): void;
}