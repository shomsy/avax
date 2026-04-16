<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\ProvisionUser;

use Avax\Auth\System\Capability\Scim\ScimAccountState;
use SensitiveParameter;

final readonly class ProvisionScimUserData
{
    public ScimAccountState $state;
    /** @var list<string> */
    public array            $groups;
    public string           $username;
    public string           $email;
    public string           $externalId;
    public string           $directoryToken;
    public string           $directoryId;

    /**
     * @param list<string> $groups
     */
    public function __construct(
        string                        $directoryId,
        #[SensitiveParameter] string  $directoryToken,
        string                        $externalId,
        #[\SensitiveParameter] string $email,
        string                        $username,
        array|null                    $groups = null,
        ScimAccountState              $state = ScimAccountState::ACTIVE
    )
    {
        $groups               ??= [];
        $this->directoryId    = $directoryId;
        $this->directoryToken = $directoryToken;
        $this->externalId     = $externalId;
        $this->email          = $email;
        $this->username       = $username;
        $this->groups         = $groups;
        $this->state          = $state;
    }
}
