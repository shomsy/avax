<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Scim\ProvisionUser;

use Avax\Auth\System\Capabilities\Scim\ScimAccountState;

final readonly class ScimProvisioningResult
{
    public bool             $driftDetected;
    public bool             $idempotent;
    public bool             $updated;
    public bool             $created;
    /** @var list<string> */
    public array            $roles;
    public ScimAccountState $state;
    public string           $externalId;
    public int              $userId;

    /**
     * @param list<string> $roles
     */
    public function __construct(
        int              $userId,
        string           $externalId,
        ScimAccountState $state,
        array            $roles,
        bool             $created,
        bool             $updated,
        bool             $idempotent,
        bool             $driftDetected
    )
    {
        $this->userId        = $userId;
        $this->externalId    = $externalId;
        $this->state         = $state;
        $this->roles         = $roles;
        $this->created       = $created;
        $this->updated       = $updated;
        $this->idempotent    = $idempotent;
        $this->driftDetected = $driftDetected;
    }
}
