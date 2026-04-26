<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\SyncGroups;

use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimAccountState;
use SensitiveParameter;

final readonly class SyncScimGroupsData
{
    /**
     * @param list<string> $groups
     */
    public function __construct(
        public string                       $directoryId,
        #[SensitiveParameter] public string $directoryToken,
        public string                       $externalId,
        public array                        $groups,
        public ScimAccountState             $state = ScimAccountState::ACTIVE
    ) {}
}
