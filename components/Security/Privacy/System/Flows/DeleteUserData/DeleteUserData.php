<?php

declare(strict_types=1);

namespace Avax\Components\Security\Privacy\System\Flows\DeleteUserData;

use Avax\Components\Security\Privacy\System\Capabilities\DataDeleter\DataDeleter;

final readonly class DeleteUserData
{
    public function __construct(
        private DataDeleter $deleter = new DataDeleter(),
    ) {}

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function execute(array $data, string $userId, bool $anonymize = false) : array
    {
        return $anonymize
            ? $this->deleter->anonymize(data: $data, userId: $userId)
            : $this->deleter->hardDelete(data: $data, userId: $userId);
    }
}
