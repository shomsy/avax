<?php
declare(strict_types=1);
namespace Avax\Components\DataLayer\AccessPersistentData;

final class PersistentDataResult
{
    public function __construct(
        public array $rows,
        public int $affectedRows = 0
    ) {}
}
