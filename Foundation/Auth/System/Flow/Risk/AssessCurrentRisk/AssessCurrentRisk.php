<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Risk\AssessCurrentRisk;

use Avax\Auth\System\Capability\Risk\DeterministicRiskEngine;
use Avax\Auth\System\Capability\Risk\RiskDecision;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;

final readonly class AssessCurrentRisk
{
    public function __construct(
        private CurrentAuthentication $currentAuthentication,
        private UserSourceInterface $userSource,
        private DeterministicRiskEngine $riskEngine
    ) {}

    public function execute(string|null $ipAddress = null, string|null $userAgent = null) : RiskDecision|null
    {
        $user = $this->currentAuthentication->read()->user();

        if ($user === null) {
            return null;
        }

        $entity = $this->userSource->findById(new UserId($user->id));

        if ($entity === null) {
            return null;
        }

        return $this->riskEngine->assessSuccessfulAuthentication($entity, $ipAddress, $userAgent);
    }
}
