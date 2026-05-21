<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\PublicSurface;

use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\AdminElevationRecord;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\AdminElevationStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\InMemoryAdminElevationStore;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use DateTimeImmutable;
use RuntimeException;

/**
 * Admin — admin elevation operations within tenancy.
 */
final readonly class Admin
{
    public function __construct(
        private AdminElevationStoreInterface $store,
        private Clock                        $clock,
    ) {}

    public function beginElevation(string $bindingId, int $userId, DateTimeImmutable|null $expiresAt = null) : AdminElevationRecord
    {
        $expiresAt ??= $this->clock->now()->modify('+15 minutes');

        $record = new AdminElevationRecord(
            bindingId : $bindingId,
            userId    : $userId,
            expiresAt : $expiresAt,
        );

        $this->store->start(adminElevationRecord: $record);

        return $record;
    }

    public function endElevation(string $bindingId) : void
    {
        $this->store->revoke(bindingId: $bindingId);
    }

    public function requireElevation(string $bindingId, int $userId) : AdminElevationRecord
    {
        $record = $this->store->find(bindingId: $bindingId);

        if (! $record instanceof AdminElevationRecord || $record->userId !== $userId || $record->isExpiredAt(moment: $this->clock->now())) {
            $this->store->revoke(bindingId: $bindingId);
            throw new RuntimeException(message: 'Admin elevation required.', code: 403);
        }

        return $record;
    }
}
