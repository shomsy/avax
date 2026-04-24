<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\Access;

use Avax\Auth\System\Capabilities\Access\Access;
use Avax\Auth\System\Capabilities\Access\Facades\Authorization;
use Avax\Auth\System\Capabilities\Access\Policy\AccessPolicy;
use Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Runtime\AssessCurrentRisk\AssessCurrentRisk;
use Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Runtime\ReadRiskSignals\ReadRiskSignals;
use Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support\DeterministicRiskEngine;
use Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support\InMemoryKnownAuthenticationEnvironmentStore;
use Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support\InMemoryRiskSignalStore;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\NullAuditLog;
use Avax\Auth\System\Capabilities\Access\RequireAccessPolicy\RequireAccessPolicy;
use Avax\Auth\System\Capabilities\Access\RequireAuthentication\RequireAuthentication;
use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Access\RequirePermission\PermissionDenied;
use Avax\Auth\System\Capabilities\Access\RequirePermission\RequirePermission;
use Avax\Auth\System\Capabilities\Access\RequirePhishingResistantAuthentication\RequirePhishingResistantAuthentication;
use Avax\Auth\System\Capabilities\Access\RequireResourceOwner\RequireResourceOwner;
use Avax\Auth\System\Capabilities\Access\RequireRole\RequireRole;
use Avax\Auth\System\Capabilities\Access\RequireRole\RoleDenied;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Stores\InMemoryMfaStore;
use Avax\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Auth\System\Capabilities\Identity\User\UserRole;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\BeginAdminElevation\BeginAdminElevation;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\EndAdminElevation\EndAdminElevation;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\RequireAdminElevation\RequireAdminElevation;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmSupport\AdminElevationRecord;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmSupport\InMemoryAdminElevationStore;
use Avax\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticateRequest;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flows\CheckAuthentication\CheckAuthentication;
use Avax\Auth\System\Flows\CheckAuthentication\ReadCurrentUser\ReadCurrentUser;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\InMemoryEmailVerificationStateStore;
use Avax\Auth\System\Foundation\Clock;
use Avax\Tests\TestCase;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for the Access façade.
 */
class AccessTest extends TestCase
{
    /**
     * @throws Unauthenticated
     * @throws PermissionDenied
     * @throws RoleDenied
     */
    public function testAccessFacadeDelegatesToBoundaries() : void
    {
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user         : new AuthenticatedUser(
                               id         : 42,
                               email      : 'access@example.com',
                               username   : 'access',
                               roles      : [UserRole::ADMIN->value],
                               permissions: ['write'],
                               mfaEnabled : true
                           ),
            mode         : AuthenticationMode::SESSION,
            sessionId    : 'session-42',
            mfaVerifiedAt: new DateTimeImmutable()
        ));
        $adminElevationStore = new InMemoryAdminElevationStore();
        $adminElevationStore->start(record: new AdminElevationRecord(
                                                userId   : 42,
                                                bindingId: 'session-42',
                                                expiresAt: new DateTimeImmutable(datetime: '+10 minutes')
                                            ));
        $clock                                  = new Clock();
        $requireAuthentication                  = new RequireAuthentication(currentAuthentication: $currentAuthentication);
        $requireRole                            = new RequireRole(currentAuthentication: $currentAuthentication);
        $requirePermission                      = new RequirePermission(currentAuthentication: $currentAuthentication);
        $requirePhishingResistantAuthentication = new RequirePhishingResistantAuthentication(currentAuthentication: $currentAuthentication);

        $authorization = new Authorization(
            requireAuthentication: $requireAuthentication,
            requireRole          : $requireRole,
            requirePermission    : $requirePermission,
            requireAccessPolicy  : new RequireAccessPolicy(
                                       requireAuthentication                 : $requireAuthentication,
                                       requireRole                           : $requireRole,
                                       requirePermission                     : $requirePermission,
                                       requireResourceOwner                  : new RequireResourceOwner(currentAuthentication: $currentAuthentication),
                                       requirePhishingResistantAuthentication: $requirePhishingResistantAuthentication,
                                       requireFreshMfa                       : new RequireFreshMfa(currentAuthentication: $currentAuthentication, clock: $clock),
                                       requireAdminElevation                 : new RequireAdminElevation(currentAuthentication: $currentAuthentication, elevationStore: $adminElevationStore, clock: $clock)
                                   )
        );

        $access = new Access(
            authenticateRequest  : new AuthenticateRequest(
                                       currentAuthentication   : $currentAuthentication,
                                       projectAuthenticatedUser: new ProjectAuthenticatedUser(
                                                                     emailVerificationState: new InMemoryEmailVerificationStateStore(),
                                                                     mfaStore              : new InMemoryMfaStore()
                                                                 ),
                                       userSource              : new InMemoryUserSource(),
                                       auditLog                : new NullAuditLog(),
                                       clock                   : $clock
                                   ),
            currentAuthentication: $currentAuthentication,
            checkAuthentication  : new CheckAuthentication(currentAuthentication: $currentAuthentication),
            readCurrentUser      : new ReadCurrentUser(currentAuthentication: $currentAuthentication),
            access               : $authorization,
            beginAdminElevation  : new BeginAdminElevation(
                                       currentAuthentication: $currentAuthentication,
                                       requireFreshMfa      : new RequireFreshMfa(currentAuthentication: $currentAuthentication, clock: $clock),
                                       elevationStore       : $adminElevationStore,
                                       auditLog             : new NullAuditLog(),
                                       clock                : $clock
                                   ),
            endAdminElevation    : new EndAdminElevation(
                                       currentAuthentication: $currentAuthentication,
                                       elevationStore       : $adminElevationStore,
                                       auditLog             : new NullAuditLog(),
                                       clock                : $clock
                                   ),
            requireAdminElevation: new RequireAdminElevation(
                                       currentAuthentication: $currentAuthentication,
                                       elevationStore       : $adminElevationStore,
                                       clock                : $clock
                                   ),
            assessCurrentRisk    : new AssessCurrentRisk(
                                       currentAuthentication: $currentAuthentication,
                                       userSource           : new InMemoryUserSource(),
                                       riskEngine           : new DeterministicRiskEngine(
                                                                  knownEnvironments: new InMemoryKnownAuthenticationEnvironmentStore(),
                                                                  signals          : new InMemoryRiskSignalStore(),
                                                                  clock            : $clock
                                                              )
                                   ),
            readRiskSignals      : new ReadRiskSignals(
                                       currentAuthentication: $currentAuthentication,
                                       riskEngine           : new DeterministicRiskEngine(
                                                                  knownEnvironments: new InMemoryKnownAuthenticationEnvironmentStore(),
                                                                  signals          : new InMemoryRiskSignalStore(),
                                                                  clock            : $clock
                                                              )
                                   )
        );

        $access->access()->requireAuthentication();
        $access->access()->requireRole(requiredRole: UserRole::USER);
        $access->access()->requirePermission(permission: new UserPermission(value: 'write'));
        $access->access()->requirePolicy(policy: new AccessPolicy(
                                                     requiredRole       : UserRole::USER,
                                                     requiredPermission : new UserPermission(value: 'write'),
                                                     resourceOwnerUserId: 42,
                                                     freshMfa           : true,
                                                     adminElevation     : true
                                                 ));

        $this->assertTrue(condition: $access->check());
        $this->assertSame(expected: 42, actual: $access->current()->user()?->id);
        $this->assertSame(expected: 42, actual: $access->user()?->id);
    }
}
