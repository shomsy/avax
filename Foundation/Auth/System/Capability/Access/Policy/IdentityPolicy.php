<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Access\Policy;

/**
 * Explicit assurance matrix entry for one actor lane.
 */
final readonly class IdentityPolicy
{
    /**
     * @param list<AuthenticationFactor> $allowedFactors
     * @param list<AuthenticationFactor> $requiredFactors
     */
    public function __construct(
        public IdentityActor   $actor,
        public AssuranceTier   $assuranceTier,
        public array           $allowedFactors,
        public array           $requiredFactors,
        public int             $idleTimeoutSeconds,
        public int             $absoluteTimeoutSeconds,
        public int|null        $freshMfaMaxAgeSeconds,
        public RecoveryPath    $recoveryPath,
        public bool            $phishingResistantRequired = false,
        public bool            $adminElevationRequired = false,
        public bool            $senderConstrainedTokensRequired = false,
        public bool            $denyByDefault = true,
        public bool            $resourceChecksRequired = true,
        public bool            $separationOfDutiesRequired = false,
        public bool            $privilegedApprovalRequired = false
    ) {}

    public function allowsFactor(AuthenticationFactor $factor) : bool
    {
        return in_array($factor, $this->allowedFactors, true);
    }

    public function requiresFactor(AuthenticationFactor $factor) : bool
    {
        return in_array($factor, $this->requiredFactors, true);
    }
}
