<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\Policy;

/**
 * Package-owned assurance defaults for the main identity lanes.
 */
final class IdentityPolicyCatalog
{
    /**
     * @return list<IdentityPolicy>
     */
    public static function all() : array
    {
        return [
            self::user(),
            self::privilegedUser(),
            self::admin(),
            self::support(),
            self::tenantAdmin(),
            self::machineIdentity(),
            self::breakGlass(),
        ];
    }

    public static function user() : IdentityPolicy
    {
        return new IdentityPolicy(
            actor                 : IdentityActor::USER,
            assuranceTier         : AssuranceTier::STANDARD,
            allowedFactors        : [
                                        AuthenticationFactor::PASSWORD,
                                        AuthenticationFactor::TOTP,
                                        AuthenticationFactor::BACKUP_CODE,
                                        AuthenticationFactor::PASSKEY,
                                        AuthenticationFactor::FEDERATED_SSO,
                                    ],
            requiredFactors       : [
                                        AuthenticationFactor::PASSWORD,
                                    ],
            idleTimeoutSeconds    : 1_800,
            absoluteTimeoutSeconds: 43_200,
            freshMfaMaxAgeSeconds : null,
            recoveryPath          : RecoveryPath::PASSWORD_RESET,
        );
    }

    public static function privilegedUser() : IdentityPolicy
    {
        return new IdentityPolicy(
            actor                 : IdentityActor::PRIVILEGED_USER,
            assuranceTier         : AssuranceTier::HIGH,
            allowedFactors        : [
                                        AuthenticationFactor::PASSWORD,
                                        AuthenticationFactor::TOTP,
                                        AuthenticationFactor::BACKUP_CODE,
                                        AuthenticationFactor::PASSKEY,
                                        AuthenticationFactor::FEDERATED_SSO,
                                    ],
            requiredFactors       : [
                                        AuthenticationFactor::PASSWORD,
                                        AuthenticationFactor::TOTP,
                                    ],
            idleTimeoutSeconds    : 900,
            absoluteTimeoutSeconds: 28_800,
            freshMfaMaxAgeSeconds : 300,
            recoveryPath          : RecoveryPath::MFA_RECOVERY,
        );
    }

    public static function admin() : IdentityPolicy
    {
        return new IdentityPolicy(
            actor                     : IdentityActor::ADMIN,
            assuranceTier             : AssuranceTier::PHISHING_RESISTANT,
            allowedFactors            : [
                                            AuthenticationFactor::PASSKEY,
                                            AuthenticationFactor::FEDERATED_SSO,
                                            AuthenticationFactor::PASSWORD,
                                            AuthenticationFactor::TOTP,
                                            AuthenticationFactor::BACKUP_CODE,
                                        ],
            requiredFactors           : [
                                            AuthenticationFactor::PASSKEY,
                                        ],
            idleTimeoutSeconds        : 600,
            absoluteTimeoutSeconds    : 14_400,
            freshMfaMaxAgeSeconds     : 180,
            recoveryPath              : RecoveryPath::ADMIN_APPROVAL,
            phishingResistantRequired : true,
            adminElevationRequired    : true,
            separationOfDutiesRequired: true,
            privilegedApprovalRequired: true,
        );
    }

    public static function support() : IdentityPolicy
    {
        return new IdentityPolicy(
            actor                     : IdentityActor::SUPPORT,
            assuranceTier             : AssuranceTier::HIGH,
            allowedFactors            : [
                                            AuthenticationFactor::PASSWORD,
                                            AuthenticationFactor::TOTP,
                                            AuthenticationFactor::PASSKEY,
                                            AuthenticationFactor::FEDERATED_SSO,
                                        ],
            requiredFactors           : [
                                            AuthenticationFactor::PASSWORD,
                                            AuthenticationFactor::TOTP,
                                        ],
            idleTimeoutSeconds        : 600,
            absoluteTimeoutSeconds    : 21_600,
            freshMfaMaxAgeSeconds     : 180,
            recoveryPath              : RecoveryPath::ADMIN_APPROVAL,
            separationOfDutiesRequired: true,
            privilegedApprovalRequired: true,
        );
    }

    public static function tenantAdmin() : IdentityPolicy
    {
        return new IdentityPolicy(
            actor                     : IdentityActor::TENANT_ADMIN,
            assuranceTier             : AssuranceTier::PHISHING_RESISTANT,
            allowedFactors            : [
                                            AuthenticationFactor::PASSKEY,
                                            AuthenticationFactor::FEDERATED_SSO,
                                            AuthenticationFactor::PASSWORD,
                                            AuthenticationFactor::TOTP,
                                        ],
            requiredFactors           : [
                                            AuthenticationFactor::PASSKEY,
                                        ],
            idleTimeoutSeconds        : 900,
            absoluteTimeoutSeconds    : 21_600,
            freshMfaMaxAgeSeconds     : 180,
            recoveryPath              : RecoveryPath::ADMIN_APPROVAL,
            phishingResistantRequired : true,
            separationOfDutiesRequired: true,
        );
    }

    public static function machineIdentity() : IdentityPolicy
    {
        return new IdentityPolicy(
            actor                          : IdentityActor::MACHINE_IDENTITY,
            assuranceTier                  : AssuranceTier::HIGH,
            allowedFactors                 : [
                                                 AuthenticationFactor::DPOP,
                                                 AuthenticationFactor::MTLS,
                                             ],
            requiredFactors                : [
                                                 AuthenticationFactor::MTLS,
                                             ],
            idleTimeoutSeconds             : 300,
            absoluteTimeoutSeconds         : 3_600,
            freshMfaMaxAgeSeconds          : null,
            recoveryPath                   : RecoveryPath::NONE,
            senderConstrainedTokensRequired: true,
        );
    }

    public static function breakGlass() : IdentityPolicy
    {
        return new IdentityPolicy(
            actor                     : IdentityActor::BREAK_GLASS,
            assuranceTier             : AssuranceTier::EMERGENCY,
            allowedFactors            : [
                                            AuthenticationFactor::PASSKEY,
                                        ],
            requiredFactors           : [
                                            AuthenticationFactor::PASSKEY,
                                        ],
            idleTimeoutSeconds        : 300,
            absoluteTimeoutSeconds    : 1_800,
            freshMfaMaxAgeSeconds     : 60,
            recoveryPath              : RecoveryPath::ADMIN_APPROVAL,
            phishingResistantRequired : true,
            separationOfDutiesRequired: true,
            privilegedApprovalRequired: true,
        );
    }
}
