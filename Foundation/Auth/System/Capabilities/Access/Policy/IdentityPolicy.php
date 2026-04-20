<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\Policy;

/**
 * Explicit assurance matrix entry for one actor lane.
 */
final readonly class IdentityPolicy
{
    public bool          $privilegedApprovalRequired;
    public bool          $separationOfDutiesRequired;
    public bool          $resourceChecksRequired;
    public bool          $denyByDefault;
    public bool          $senderConstrainedTokensRequired;
    public bool          $adminElevationRequired;
    public bool          $phishingResistantRequired;
    public RecoveryPath  $recoveryPath;
    public int|null      $freshMfaMaxAgeSeconds;
    public int           $absoluteTimeoutSeconds;
    public int           $idleTimeoutSeconds;
    /** @var list<AuthenticationFactor> */
    public array         $requiredFactors;
    /** @var list<AuthenticationFactor> */
    public array         $allowedFactors;
    public AssuranceTier $assuranceTier;
    public IdentityActor $actor;

    /**
     * @param list<AuthenticationFactor> $allowedFactors
     * @param list<AuthenticationFactor> $requiredFactors
     */
    public function __construct(
        IdentityActor $actor,
        AssuranceTier $assuranceTier,
        array         $allowedFactors,
        array         $requiredFactors,
        int           $idleTimeoutSeconds,
        int           $absoluteTimeoutSeconds,
        int|null      $freshMfaMaxAgeSeconds,
        RecoveryPath  $recoveryPath,
        bool|null     $phishingResistantRequired = null,
        bool|null     $adminElevationRequired = null,
        bool|null     $senderConstrainedTokensRequired = null,
        bool|null     $denyByDefault = null,
        bool|null     $resourceChecksRequired = null,
        bool|null     $separationOfDutiesRequired = null,
        bool          $privilegedApprovalRequired = false
    )
    {
        $phishingResistantRequired             ??= false;
        $adminElevationRequired                ??= false;
        $senderConstrainedTokensRequired       ??= false;
        $denyByDefault                         ??= true;
        $resourceChecksRequired                ??= true;
        $separationOfDutiesRequired            ??= false;
        $this->actor                           = $actor;
        $this->assuranceTier                   = $assuranceTier;
        $this->allowedFactors                  = $allowedFactors;
        $this->requiredFactors                 = $requiredFactors;
        $this->idleTimeoutSeconds              = $idleTimeoutSeconds;
        $this->absoluteTimeoutSeconds          = $absoluteTimeoutSeconds;
        $this->freshMfaMaxAgeSeconds           = $freshMfaMaxAgeSeconds;
        $this->recoveryPath                    = $recoveryPath;
        $this->phishingResistantRequired       = $phishingResistantRequired;
        $this->adminElevationRequired          = $adminElevationRequired;
        $this->senderConstrainedTokensRequired = $senderConstrainedTokensRequired;
        $this->denyByDefault                   = $denyByDefault;
        $this->resourceChecksRequired          = $resourceChecksRequired;
        $this->separationOfDutiesRequired      = $separationOfDutiesRequired;
        $this->privilegedApprovalRequired      = $privilegedApprovalRequired;
    }

    public function allowsFactor(AuthenticationFactor $factor) : bool
    {
        return in_array($factor, $this->allowedFactors, true);
    }

    public function requiresFactor(AuthenticationFactor $factor) : bool
    {
        return in_array($factor, $this->requiredFactors, true);
    }
}
