<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadUsers;

use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimAccountState;
use SensitiveParameter;

final readonly class ScimUserProjection
{
    /**
     * @param list<string> $roles
     * @param list<string> $groups
     */
    public function __construct(
        public string                       $externalId,
        public int                          $userId,
        #[SensitiveParameter] public string $email,
        public string                       $username,
        public array                        $roles,
        public array                        $groups,
        public ScimAccountState             $state
    ) {}
}
