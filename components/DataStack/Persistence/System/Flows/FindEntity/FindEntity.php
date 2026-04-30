<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Flows\FindEntity;

use Avax\Components\DataStack\Persistence\System\Capabilities\Repositories\Repository;

final class FindEntity
{
    public function find(Repository $repository, string $id) : object|null
    {
        return $repository->findById($id);
    }
}
