<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Model;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class TenantInvite
{
    public function __construct(
        public string                 $inviteId,
        public string                 $tenantId,
        #[SensitiveParameter]
        public string                 $email,
        public TenantMemberRole       $role,
        #[SensitiveParameter]
        public string                 $tokenHash,
        public string                 $invitedBy,
        public DateTimeImmutable      $createdAt,
        public DateTimeImmutable|null $acceptedAt = null,
        public int|null               $acceptedByUserId = null,
    ) {}

    public function isAccepted() : bool
    {
        return $this->acceptedAt !== null;
    }
}
