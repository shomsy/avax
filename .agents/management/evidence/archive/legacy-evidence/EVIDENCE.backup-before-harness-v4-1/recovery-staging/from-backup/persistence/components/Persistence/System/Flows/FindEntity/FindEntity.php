<?php

declare(strict_types=1);

namespace Avax\Components\Persistence\System\Flows\FindEntity;

use Avax\Components\Persistence\System\Capabilities\Repositories\Repository;

final class FindEntity
{
    public function find(Repository $repository, string $id) : ?object
    {
        return $repository->findById($id);
    }
}
