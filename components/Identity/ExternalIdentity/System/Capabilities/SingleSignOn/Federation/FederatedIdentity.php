<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationSupport;

use SensitiveParameter;

final readonly class FederatedIdentity
{
    /** @var list<string> */
    public array $groups;

    /**
     * @param list<string> $groups
     */
    public function __construct(
        public string $subject,
        #[SensitiveParameter]
        public string $email,
        public string $displayName,
        array         $groups = null,
        public bool   $emailVerified = true,
    )
    {
        $groups       ??= [];
        $this->groups = $groups;
    }
}
