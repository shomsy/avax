<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\System\Capabilities\RiskBasedAccess\Signals;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserRole;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Small deterministic risk engine for environment changes and token abuse.
 */
final readonly class DeterministicRiskEngine
{
    public function __construct(private KnownAuthenticationEnvironmentStoreInterface $knownAuthenticationEnvironmentStore, private RiskSignalStoreInterface $riskSignalStore, private Clock $clock) {}

    public function assessSuccessfulAuthentication(
        User    $user,
        #[SensitiveParameter]
        ?string $ipAddress,
        ?string $userAgent,
    ) : RiskDecision
    {
        $userId = $user->getId()->value;

        if (! $this->knownAuthenticationEnvironmentStore->hasSeen(userId: $userId, ipAddress: $ipAddress, userAgent: $userAgent)) {
            $this->riskSignalStore->record(signal: new RiskSignal(
                                                       userId    : $userId,
                                                       name      : 'new_environment',
                                                       occurredAt: $this->clock->now(),
                                                       context   : [
                                                                       'ip_address' => $ipAddress,
                                                                       'user_agent' => $userAgent,
                                                                   ],
                                                   ));
            $this->knownAuthenticationEnvironmentStore->remember(userId: $userId, ipAddress: $ipAddress, userAgent: $userAgent);

            if ($user->hasRole(role: UserRole::ADMIN)) {
                return new RiskDecision(action: RiskAction::OPEN_REVIEW, reasons: ['admin_new_environment']);
            }

            return new RiskDecision(action: RiskAction::ALLOW, reasons: ['new_environment']);
        }

        return RiskDecision::allow('known_environment');
    }

    public function recordRefreshReuse(int $userId, ?string $clientId) : RiskDecision
    {
        $this->riskSignalStore->record(signal: new RiskSignal(
                                                   userId    : $userId,
                                                   name      : 'refresh_reuse_detected',
                                                   occurredAt: $this->clock->now(),
                                                   context   : [
                                                                   'client_id' => $clientId,
                                                               ],
                                               ));

        return new RiskDecision(action: RiskAction::REVOKE_SESSIONS, reasons: ['refresh_reuse_detected']);
    }

    /**
     * @return list<RiskSignal>
     */
    public function readSignalsForUser(int $userId) : array
    {
        return $this->riskSignalStore->forUser(userId: $userId);
    }
}
