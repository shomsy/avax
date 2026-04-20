<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Model;

use DateTimeImmutable;
use SensitiveParameter;

final readonly class TenantInvite
{
    public int|null               $acceptedByUserId;
    public DateTimeImmutable|null $acceptedAt;
    public DateTimeImmutable      $createdAt;
    public string                 $invitedBy;
    public string                 $tokenHash;
    public TenantMemberRole       $role;
    public string                 $email;
    public string                 $tenantId;
    public string                 $inviteId;

    public function __construct(
        string                       $inviteId,
        string                       $tenantId,
        #[SensitiveParameter] string $email,
        TenantMemberRole             $role,
        #[SensitiveParameter] string $tokenHash,
        string                       $invitedBy,
        DateTimeImmutable            $createdAt,
        DateTimeImmutable|null       $acceptedAt = null,
        int|null                     $acceptedByUserId = null
    )
    {
        $this->inviteId         = $inviteId;
        $this->tenantId         = $tenantId;
        $this->email            = $email;
        $this->role             = $role;
        $this->tokenHash        = $tokenHash;
        $this->invitedBy        = $invitedBy;
        $this->createdAt        = $createdAt;
        $this->acceptedAt       = $acceptedAt;
        $this->acceptedByUserId = $acceptedByUserId;
    }

    public function isAccepted() : bool
    {
        return $this->acceptedAt !== null;
    }
}
