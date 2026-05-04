<?php
declare(strict_types=1);
namespace Avax\Components\DataLayer\AccessPersistentData;

final class PersistentDataRequest
{
    public function __construct(
        public string $statement,
        public array $parameters = []
    ) {}
}
