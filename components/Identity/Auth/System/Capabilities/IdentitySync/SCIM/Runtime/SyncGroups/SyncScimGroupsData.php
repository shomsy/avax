<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\SyncGroups;

use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimAccountState;
use SensitiveParameter;

final readonly class SyncScimGroupsData
{
    /**
     * @param list<string> $groups
     */
    public function __construct(
        public string           $directoryId,
        #[SensitiveParameter]
        public string           $directoryToken,
        public string           $externalId,
        public array            $groups,
        public ScimAccountState $state = ScimAccountState::ACTIVE,
    ) {}
}
