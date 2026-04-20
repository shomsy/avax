<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support;

use Avax\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;

final readonly class ScimProvisionedIdentity
{
    public DateTimeImmutable $synchronizedAt;
    public ScimAccountState  $state;
    /** @var list<string> */
    public array             $groups;
    public string            $fingerprint;
    public UserId            $userId;
    public string            $externalId;
    public string            $directoryId;

    /**
     * @param list<string> $groups
     */
    public function __construct(
        string            $directoryId,
        string            $externalId,
        UserId            $userId,
        string            $fingerprint,
        array             $groups,
        ScimAccountState  $state,
        DateTimeImmutable $synchronizedAt
    )
    {
        $this->directoryId    = $directoryId;
        $this->externalId     = $externalId;
        $this->userId         = $userId;
        $this->fingerprint    = $fingerprint;
        $this->groups         = $groups;
        $this->state          = $state;
        $this->synchronizedAt = $synchronizedAt;
    }
}
