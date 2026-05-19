<?php

declare(strict_types=1);

namespace Avax\Components\Persistence\System\Flows\SaveEntity;

use Avax\Components\Persistence\System\Capabilities\Repositories\Repository;

final class SaveEntity
{
    public function save(Repository $repository, object $entity) : void
    {
        $repository->save($entity);
    }
}
