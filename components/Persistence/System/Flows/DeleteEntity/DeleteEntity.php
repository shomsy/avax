<?php

declare(strict_types=1);

namespace Avax\Components\Persistence\System\Flows\DeleteEntity;

use Avax\Components\Persistence\System\Capabilities\Repositories\Repository;

final class DeleteEntity
{
    public function delete(Repository $repository, object $entity): void
    {
        $repository->delete($entity);
    }
}