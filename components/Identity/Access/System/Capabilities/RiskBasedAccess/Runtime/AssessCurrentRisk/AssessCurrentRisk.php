<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Runtime\AssessCurrentRisk;

use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\DeterministicRiskEngine;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\RiskDecision;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

final readonly class AssessCurrentRisk
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication $currentAuthentication,
        private UserSourceInterface $userSource,
        private DeterministicRiskEngine $deterministicRiskEngine,
    ) {}

    public function execute(#[SensitiveParameter] ?string $ipAddress = null, ?string $userAgent = null) : ?RiskDecision
    {
        $user = $this->currentAuthentication->read()->user();

        if (! $user instanceof AuthenticatedUser) {
            return null;
        }

        $entity = $this->userSource->findById(id: new UserId(value: $user->id));

        if (! $entity instanceof User) {
            return null;
        }

        return $this->deterministicRiskEngine->assessSuccessfulAuthentication(user: $entity, ipAddress: $ipAddress, userAgent: $userAgent);
    }
}
