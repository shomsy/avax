<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Flows\DeleteEntity;

use Avax\Components\DataStack\Persistence\System\Capabilities\PersistenceRepositories\Repository;

final class DeleteEntity
{
    public function delete(Repository $repository, object $entity): void
    {
        $repository->delete($entity);
    }
}
