<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use DateTimeImmutable;

final readonly class ScimProvisionedIdentity
{
    /**
     * @param list<string> $groups
     */
    public function __construct(public string $directoryId, public string $externalId, public UserId $userId, public string $fingerprint, public array $groups, public ScimAccountState $state, public DateTimeImmutable $synchronizedAt) {}
}
