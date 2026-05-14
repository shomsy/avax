<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Flows\SaveEntity;

use Avax\Components\DataStack\Persistence\System\Capabilities\PersistenceRepositories\Repository;

final class SaveEntity
{
    public function save(Repository $repository, object $entity): void
    {
        $repository->save($entity);
    }
}
