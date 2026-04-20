<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Risk\AssessCurrentRisk;

use Avax\Auth\System\Capabilities\Risk\DeterministicRiskEngine;
use Avax\Auth\System\Capabilities\Risk\RiskDecision;
use Avax\Auth\System\Capabilities\User\UserId;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;
use Avax\Auth\System\Flows\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

final readonly class AssessCurrentRisk
{
    private DeterministicRiskEngine $riskEngine;
    private UserSourceInterface     $userSource;
    private CurrentAuthentication   $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication $currentAuthentication,
        UserSourceInterface                         $userSource,
        DeterministicRiskEngine                     $riskEngine
    )
    {
        $this->currentAuthentication = $currentAuthentication;
        $this->userSource            = $userSource;
        $this->riskEngine            = $riskEngine;
    }

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
