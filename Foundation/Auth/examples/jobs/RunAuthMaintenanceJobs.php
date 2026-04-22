<?php

declare(strict_types=1);

namespace Avax\Auth\Examples\Jobs;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\ExportAuditEvents\ExportAuditEvents;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\CleanupExpiredAuthorizationCodes\CleanupExpiredAuthorizationCodes;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Limit\CleanupExpiredMfaChallenges\CleanupExpiredMfaChallenges;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\CleanupExpiredPasskeyChallenges\CleanupExpiredPasskeyChallenges;
use Avax\Auth\System\Capabilities\Identity\Sessions\Runtime\CleanupExpiredSessions\CleanupExpiredSessions;
use Avax\Auth\System\Flows\RecoverAccess\PasswordReset\CleanupExpiredPasswordResets\CleanupExpiredPasswordResets;
use SensitiveParameter;

/**
 * Reference scheduler entrypoint for auth maintenance and export jobs.
 */
final readonly class RunAuthMaintenanceJobs
{
    private CleanupExpiredMfaChallenges      $cleanupExpiredMfaChallenges;

    public function __construct(
        #[SensitiveParameter] private CleanupExpiredSessions           $cleanupExpiredSessions,
        #[SensitiveParameter] private CleanupExpiredPasswordResets     $cleanupExpiredPasswordResets,
        CleanupExpiredMfaChallenges                            $cleanupExpiredMfaChallenges,
        #[SensitiveParameter] private CleanupExpiredAuthorizationCodes $cleanupExpiredAuthorizationCodes,
        private CleanupExpiredPasskeyChallenges                        $cleanupExpiredPasskeyChallenges,
        private ExportAuditEvents                                      $exportAuditEvents
    )
    {
        $this->cleanupExpiredMfaChallenges      = $cleanupExpiredMfaChallenges;
    }

    /**
     * @return array<string, int>
     */
    public function execute() : array
    {
        return [
            'sessions'              => $this->cleanupExpiredSessions->execute(),
            'password_resets'       => $this->cleanupExpiredPasswordResets->execute(),
            'mfa_challenges'        => $this->cleanupExpiredMfaChallenges->execute(),
            'authorization_codes'   => $this->cleanupExpiredAuthorizationCodes->execute(),
            'passkey_challenges'    => $this->cleanupExpiredPasskeyChallenges->execute(),
            'audit_events_exported' => $this->exportAuditEvents->execute(),
        ];
    }
}
