<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\ProvisionUser;

use Avax\Auth\System\Capability\Scim\ScimAccountState;
use SensitiveParameter;

final readonly class ProvisionScimUserData
{
    /**
     * @param list<string> $groups
     */
    public function __construct(
        public string                        $directoryId,
        #[SensitiveParameter] public string  $directoryToken,
        public string                        $externalId,
        #[\SensitiveParameter] public string $email,
        public string                        $username,
        public array                         $groups = [],
        public ScimAccountState              $state = ScimAccountState::ACTIVE
    ) {}
}
