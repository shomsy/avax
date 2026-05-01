<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities;

use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Runtime\AssessCurrentRisk\AssessCurrentRisk;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Runtime\ReadRiskSignals\ReadRiskSignals;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\RiskDecision;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\RiskSignal;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticateRequest;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationRequest;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\CheckAuthentication;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\ReadCurrentUser\ReadCurrentUser;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\AdminElevation;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\AdminElevationFailed;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\BeginAdminElevation\BeginAdminElevation;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\EndAdminElevation\EndAdminElevation;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\RequireAdminElevation\RequireAdminElevation;
use DateMalformedStringException;
use SensitiveParameter;

final readonly class Access
{
    public function __construct(
        private AuthenticateRequest $authenticateRequest,
        #[SensitiveParameter]
        private CurrentAuthentication $currentAuthentication,
        #[SensitiveParameter]
        private CheckAuthentication $checkAuthentication,
        private ReadCurrentUser $readCurrentUser,
        #[SensitiveParameter]
        private AccessInterface $access,
        private BeginAdminElevation $beginAdminElevation,
        private EndAdminElevation $endAdminElevation,
        private RequireAdminElevation $requireAdminElevation,
        private AssessCurrentRisk $assessCurrentRisk,
        private ReadRiskSignals $readRiskSignals,
    ) {}

    public function authenticateRequest(AuthenticationRequest $request): AuthenticationContext
    {
        return $this->authenticateRequest->execute(request: $request);
    }

    public function current(): AuthenticationContext
    {
        return $this->currentAuthentication->read();
    }

    public function check(): bool
    {
        return $this->checkAuthentication->execute();
    }

    public function user(): ?AuthenticatedUser
    {
        return $this->readCurrentUser->execute();
    }

    public function access(): AccessInterface
    {
        return $this->access;
    }

    /**
     * @throws DateMalformedStringException
     * @throws Unauthenticated
     */
    public function beginAdminElevation(): AdminElevation
    {
        return $this->beginAdminElevation->execute();
    }

    public function endAdminElevation(): void
    {
        $this->endAdminElevation->execute();
    }

    /**
     * @throws AdminElevationFailed
     */
    public function requireAdminElevation(): void
    {
        $this->requireAdminElevation->execute();
    }

    public function assessCurrentRisk(#[SensitiveParameter] ?string $ipAddress = null, ?string $userAgent = null): ?RiskDecision
    {
        return $this->assessCurrentRisk->execute(ipAddress: $ipAddress, userAgent: $userAgent);
    }

    /**
     * @return list<RiskSignal>
     */
    public function readRiskSignals(?int $userId = null): array
    {
        return $this->readRiskSignals->execute(userId: $userId);
    }
}
