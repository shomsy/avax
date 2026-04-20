<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport;

use SensitiveParameter;

final readonly class FederatedIdentity
{
    public bool   $emailVerified;
    /** @var list<string> */
    public array  $groups;
    public string $displayName;
    public string $email;
    public string $subject;

    /**
     * @param list<string> $groups
     */
    public function __construct(
        string                       $subject,
        #[SensitiveParameter] string $email,
        string                       $displayName,
        array|null                   $groups = null,
        bool                         $emailVerified = true
    )
    {
        $groups              ??= [];
        $this->subject       = $subject;
        $this->email         = $email;
        $this->displayName   = $displayName;
        $this->groups        = $groups;
        $this->emailVerified = $emailVerified;
    }
}
