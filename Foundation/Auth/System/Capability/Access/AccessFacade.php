<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Access;

use Avax\Auth\System\Capability\Access\AccessInterface;
use Avax\Auth\System\Capability\Risk\RiskDecision;
use Avax\Auth\System\Capability\Risk\RiskSignal;
use Avax\Auth\System\Flow\AdminRealm\AdminElevation;
use Avax\Auth\System\Flow\AdminRealm\BeginAdminElevation\BeginAdminElevation;
use Avax\Auth\System\Flow\AdminRealm\EndAdminElevation\EndAdminElevation;
use Avax\Auth\System\Flow\AdminRealm\RequireAdminElevation\RequireAdminElevation;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticateRequest;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationRequest;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\CheckAuthentication\CheckAuthentication;
use Avax\Auth\System\Flow\ReadCurrentUser\ReadCurrentUser;
use Avax\Auth\System\Flow\Risk\AssessCurrentRisk\AssessCurrentRisk;
use Avax\Auth\System\Flow\Risk\ReadRiskSignals\ReadRiskSignals;
use SensitiveParameter;

final readonly class AccessFacade
{
    public function __construct(
        private AuthenticateRequest                          $authenticateRequest,
        #[\SensitiveParameter] private CurrentAuthentication $currentAuthentication,
        #[\SensitiveParameter] private CheckAuthentication   $checkAuthentication,
        private ReadCurrentUser                              $readCurrentUser,
        #[\SensitiveParameter] private AccessInterface       $access,
        private BeginAdminElevation                          $beginAdminElevation,
        private EndAdminElevation                            $endAdminElevation,
        private RequireAdminElevation                        $requireAdminElevation,
        private AssessCurrentRisk                            $assessCurrentRisk,
        private ReadRiskSignals                              $readRiskSignals
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

    public function beginAdminElevation() : AdminElevation
    {
        return $this->beginAdminElevation->execute();
    }

    public function endAdminElevation() : void
    {
        $this->endAdminElevation->execute();
    }

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
