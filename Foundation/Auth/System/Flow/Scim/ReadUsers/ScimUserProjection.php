<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\ReadUsers;

use Avax\Auth\System\Capability\Scim\ScimAccountState;
use SensitiveParameter;

final readonly class ScimUserProjection
{
    public ScimAccountState $state;
    /** @var list<string> */
    public array            $groups;
    /** @var list<string> */
    public array            $roles;
    public string           $username;
    public string           $email;
    public int              $userId;
    public string           $externalId;

    /**
     * @param list<string> $roles
     * @param list<string> $groups
     */
    public function __construct(
        string                       $externalId,
        int                          $userId,
        #[SensitiveParameter] string $email,
        string                       $username,
        array                        $roles,
        array                        $groups,
        ScimAccountState             $state
    )
    {
        $this->externalId = $externalId;
        $this->userId     = $userId;
        $this->email      = $email;
        $this->username   = $username;
        $this->roles      = $roles;
        $this->groups     = $groups;
        $this->state      = $state;
    }
}
