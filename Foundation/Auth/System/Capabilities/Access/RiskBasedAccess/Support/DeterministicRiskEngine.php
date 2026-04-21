<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support;

use Avax\Auth\System\Capabilities\Identity\User\User;
use Avax\Auth\System\Capabilities\Identity\User\UserRole;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Small deterministic risk engine for environment changes and token abuse.
 */
final readonly class DeterministicRiskEngine
{
    private Clock                                        $clock;
    private RiskSignalStoreInterface                     $signals;
    private KnownAuthenticationEnvironmentStoreInterface $knownEnvironments;

    public function __construct(
        KnownAuthenticationEnvironmentStoreInterface $knownEnvironments,
        RiskSignalStoreInterface                     $signals,
        Clock                                        $clock
    )
    {
        $this->knownEnvironments = $knownEnvironments;
        $this->signals           = $signals;
        $this->clock             = $clock;
    }

    public function assessSuccessfulAuthentication(
        User                              $user,
        #[SensitiveParameter] string|null $ipAddress,
        string|null                       $userAgent
    ) : RiskDecision
    {
        $userId = $user->getId()->value;

        if (! $this->knownEnvironments->hasSeen(userId: $userId, ipAddress: $ipAddress, userAgent: $userAgent)) {
            $this->signals->record(signal: new RiskSignal(
                                               userId    : $userId,
                                               name      : 'new_environment',
                                               occurredAt: $this->clock->now(),
                                               context   : [
                                                               'ip_address' => $ipAddress,
                                                               'user_agent' => $userAgent,
                                                           ]
                                           ));
            $this->knownEnvironments->remember(userId: $userId, ipAddress: $ipAddress, userAgent: $userAgent);

            if ($user->hasRole(role: UserRole::ADMIN)) {
                return new RiskDecision(action: RiskAction::OPEN_REVIEW, reasons: ['admin_new_environment']);
            }

            return new RiskDecision(action: RiskAction::ALLOW, reasons: ['new_environment']);
        }

        return RiskDecision::allow('known_environment');
    }

    public function recordRefreshReuse(int $userId, string|null $clientId) : RiskDecision
    {
        $this->signals->record(signal: new RiskSignal(
                                           userId    : $userId,
                                           name      : 'refresh_reuse_detected',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'client_id' => $clientId,
                                                       ]
                                       ));

        return new RiskDecision(action: RiskAction::REVOKE_SESSIONS, reasons: ['refresh_reuse_detected']);
    }

    /**
     * @return list<RiskSignal>
     */
    public function readSignalsForUser(int $userId) : array
    {
        return $this->signals->forUser(userId: $userId);
    }
}
