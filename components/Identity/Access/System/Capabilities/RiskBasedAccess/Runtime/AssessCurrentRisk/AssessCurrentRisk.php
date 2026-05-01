<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Runtime\AssessCurrentRisk;

use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Support\DeterministicRiskEngine;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Support\RiskDecision;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

final readonly class AssessCurrentRisk
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication   $currentAuthentication,
        private UserSourceInterface     $userSource,
        private DeterministicRiskEngine $riskEngine,
    ) {}

    public function execute(#[SensitiveParameter] string|null $ipAddress = null, string|null $userAgent = null) : RiskDecision|null
    {
        $user = $this->currentAuthentication->read()->user();

        if ($user === null) {
            return null;
        }

        $entity = $this->userSource->findById(id: new UserId(value: $user->id));

        if ($entity === null) {
            return null;
        }

        return $this->riskEngine->assessSuccessfulAuthentication(user: $entity, ipAddress: $ipAddress, userAgent: $userAgent);
    }
}
