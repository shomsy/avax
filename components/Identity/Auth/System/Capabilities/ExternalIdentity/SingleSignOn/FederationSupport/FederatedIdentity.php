<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport;

use SensitiveParameter;

final readonly class FederatedIdentity
{
    /** @var list<string> */
    public array $groups;

    /**
     * @param list<string> $groups
     */
    public function __construct(
        public string                       $subject,
        #[SensitiveParameter] public string $email,
        public string                       $displayName,
        array|null                          $groups = null,
        public bool                         $emailVerified = true
    )
    {
        $groups       ??= [];
        $this->groups = $groups;
    }
}
