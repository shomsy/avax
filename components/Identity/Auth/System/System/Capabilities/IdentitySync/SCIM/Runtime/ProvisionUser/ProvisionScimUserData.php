<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser;

use Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\SCIM\Directories\ScimAccountState;
use SensitiveParameter;

final readonly class ProvisionScimUserData
{
    /** @var list<string> */
    public array $groups;

    /**
     * @param list<string> $groups
     */
    public function __construct(
        public string           $directoryId,
        #[SensitiveParameter]
        public string           $directoryToken,
        public string           $externalId,
        #[SensitiveParameter]
        public string           $email,
        public string           $username,
        array                   $groups = [],
        public ScimAccountState $state = ScimAccountState::ACTIVE,
    )
    {
        $groups       ??= [];
        $this->groups = $groups;
    }
}
