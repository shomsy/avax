<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Scim\SyncGroups;

use Avax\Auth\System\Capabilities\Scim\ScimAccountState;
use SensitiveParameter;

final readonly class SyncScimGroupsData
{
    public ScimAccountState $state;
    /** @var list<string> */
    public array            $groups;
    public string           $externalId;
    public string           $directoryToken;
    public string           $directoryId;

    /**
     * @param list<string> $groups
     */
    public function __construct(
        string                       $directoryId,
        #[SensitiveParameter] string $directoryToken,
        string                       $externalId,
        array                        $groups,
        ScimAccountState             $state = ScimAccountState::ACTIVE
    )
    {
        $this->directoryId    = $directoryId;
        $this->directoryToken = $directoryToken;
        $this->externalId     = $externalId;
        $this->groups         = $groups;
        $this->state          = $state;
    }
}
