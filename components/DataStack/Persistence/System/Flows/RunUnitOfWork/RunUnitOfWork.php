<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Flows\RunUnitOfWork;

use Avax\Components\DataStack\Persistence\System\Capabilities\UnitOfWork\UnitOfWorkInterface;
use Throwable;

/**
 * RunUnitOfWork - Flow to orchestrate a transactional persistence operation.
 */
final readonly class RunUnitOfWork
{
    public function __construct(
        private UnitOfWorkInterface $unitOfWork,
    ) {}

    public function execute(callable $operation): mixed
    {
        try {
            $result = $operation($this->unitOfWork);
            $this->unitOfWork->flush();

            return $result;
        } catch (Throwable $e) {
            $this->unitOfWork->clear();

            throw $e;
        }
    }
}
