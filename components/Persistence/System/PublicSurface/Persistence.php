<?php

declare(strict_types=1);

namespace Avax\Components\Persistence\System\PublicSurface;

use Avax\Components\Persistence\System\Capabilities\Repositories\Repository;
use Avax\Components\Persistence\System\Capabilities\UnitOfWork\UnitOfWork;

final readonly class Persistence implements PersistenceInterface
{
    public function __construct(
        private Repository $repository,
        private UnitOfWork $unitOfWork,
    ) {
    }

    public function repository(string $name): Repository
    {
        return $this->repository->forEntity(entityName: $name);
    }

    public function unitOfWork(): UnitOfWork
    {
        return $this->unitOfWork;
    }
}