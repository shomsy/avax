<?php

declare(strict_types=1);

namespace Avax\Components\Persistence\System\PublicSurface;

use Avax\Components\Persistence\System\Capabilities\Repositories\Repository;
use Avax\Components\Persistence\System\Capabilities\UnitOfWork\UnitOfWork;

interface PersistenceInterface
{
    public function repository(string $name): Repository;

    public function unitOfWork(): UnitOfWork;
}