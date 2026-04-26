<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\Access;

use components\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use components\Auth\System\Capabilities\Access\RiskBasedAccess\Runtime\AssessCurrentRisk\AssessCurrentRisk;
use components\Auth\System\Capabilities\Access\RiskBasedAccess\Runtime\ReadRiskSignals\ReadRiskSignals;
use components\Auth\System\Capabilities\Access\RiskBasedAccess\Support\RiskDecision;
use components\Auth\System\Capabilities\Access\RiskBasedAccess\Support\RiskSignal;
use components\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\AdminElevation;
use components\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\AdminElevationFailed;
use components\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\BeginAdminElevation\BeginAdminElevation;
use components\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\EndAdminElevation\EndAdminElevation;
use components\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\RequireAdminElevation\RequireAdminElevation;
use components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticateRequest;
use components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationRequest;
use components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use components\Auth\System\Flows\CheckAuthentication\CheckAuthentication;
use components\Auth\System\Flows\CheckAuthentication\ReadCurrentUser\ReadCurrentUser;
use DateMalformedStringException;
use SensitiveParameter;

final readonly class Access
{
    public function __construct(
        private AuthenticateRequest                         $authenticateRequest,
        #[SensitiveParameter] private CurrentAuthentication $currentAuthentication,
        #[SensitiveParameter] private CheckAuthentication   $checkAuthentication,
        private ReadCurrentUser                             $readCurrentUser,
        #[SensitiveParameter] private AccessInterface       $access,
        private BeginAdminElevation                         $beginAdminElevation,
        private EndAdminElevation                           $endAdminElevation,
        private RequireAdminElevation                       $requireAdminElevation,
        private AssessCurrentRisk                           $assessCurrentRisk,
        private ReadRiskSignals                             $readRiskSignals
    ) {}

    public function authenticateRequest(AuthenticationRequest $request) : AuthenticationContext
    {
        return $this->authenticateRequest->execute(request: $request);
    }

    public function current() : AuthenticationContext
    {
        return $this->currentAuthentication->read();
    }

    public function check() : bool
    {
        return $this->checkAuthentication->execute();
    }

    public function user() : AuthenticatedUser|null
    {
        return $this->readCurrentUser->execute();
    }

    public function access() : AccessInterface
    {
        return $this->access;
    }

    /**
     * @throws DateMalformedStringException
     * @throws Unauthenticated
     */
    public function beginAdminElevation() : AdminElevation
    {
        return $this->beginAdminElevation->execute();
    }

    public function endAdminElevation() : void
    {
        $this->endAdminElevation->execute();
    }

    /**
     * @throws AdminElevationFailed
     */
    public function requireAdminElevation() : void
    {
        $this->requireAdminElevation->execute();
    }

    public function assessCurrentRisk(#[SensitiveParameter] string|null $ipAddress = null, string|null $userAgent = null) : RiskDecision|null
    {
        return $this->assessCurrentRisk->execute(ipAddress: $ipAddress, userAgent: $userAgent);
    }

    /**
     * @return list<RiskSignal>
     */
    public function readRiskSignals(int|null $userId = null) : array
    {
        return $this->readRiskSignals->execute(userId: $userId);
    }
}
