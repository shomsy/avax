<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\Policy;

/**
 * Explicit assurance matrix entry for one actor lane.
 */
final readonly class IdentityPolicy
{
    public bool $separationOfDutiesRequired;
    public bool $resourceChecksRequired;
    public bool $denyByDefault;
    public bool $senderConstrainedTokensRequired;
    public bool $adminElevationRequired;
    public bool $phishingResistantRequired;

    /**
     * @param list<AuthenticationFactor> $allowedFactors
     * @param list<AuthenticationFactor> $requiredFactors
     */
    public function __construct(
        public IdentityActor $actor,
        public AssuranceTier $assuranceTier,
        public array         $allowedFactors,
        public array         $requiredFactors,
        public int           $idleTimeoutSeconds,
        public int           $absoluteTimeoutSeconds,
        public int|null      $freshMfaMaxAgeSeconds,
        public RecoveryPath  $recoveryPath,
        bool|null            $phishingResistantRequired = null,
        bool|null            $adminElevationRequired = null,
        bool|null            $senderConstrainedTokensRequired = null,
        bool|null            $denyByDefault = null,
        bool|null            $resourceChecksRequired = null,
        bool|null            $separationOfDutiesRequired = null,
        public bool          $privilegedApprovalRequired = false
    )
    {
        $phishingResistantRequired             ??= false;
        $adminElevationRequired                ??= false;
        $senderConstrainedTokensRequired       ??= false;
        $denyByDefault                         ??= true;
        $resourceChecksRequired                ??= true;
        $separationOfDutiesRequired            ??= false;
        $this->phishingResistantRequired       = $phishingResistantRequired;
        $this->adminElevationRequired          = $adminElevationRequired;
        $this->senderConstrainedTokensRequired = $senderConstrainedTokensRequired;
        $this->denyByDefault                   = $denyByDefault;
        $this->resourceChecksRequired          = $resourceChecksRequired;
        $this->separationOfDutiesRequired      = $separationOfDutiesRequired;
    }

    public function allowsFactor(AuthenticationFactor $factor) : bool
    {
        return in_array(needle: $factor, haystack: $this->allowedFactors, strict: true);
    }

    public function requiresFactor(AuthenticationFactor $factor) : bool
    {
        return in_array(needle: $factor, haystack: $this->requiredFactors, strict: true);
    }
}
