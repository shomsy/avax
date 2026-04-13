<?php

declare(strict_types=1);

namespace Avax\Auth\Examples\Jobs;

use Avax\Auth\System\Flow\Diagnostics\ExportAuditEvents\ExportAuditEvents;
use Avax\Auth\System\Flow\Mfa\Challenge\CleanupExpiredMfaChallenges\CleanupExpiredMfaChallenges;
use Avax\Auth\System\Flow\OAuth\CleanupExpiredAuthorizationCodes\CleanupExpiredAuthorizationCodes;
use Avax\Auth\System\Flow\Passkey\CleanupExpiredPasskeyChallenges\CleanupExpiredPasskeyChallenges;
use Avax\Auth\System\Flow\Recover\CleanupExpiredPasswordResets\CleanupExpiredPasswordResets;
use Avax\Auth\System\Flow\Session\CleanupExpiredSessions\CleanupExpiredSessions;

/**
 * Reference scheduler entrypoint for auth maintenance and export jobs.
 */
final readonly class RunAuthMaintenanceJobs
{
    public function __construct(
        private CleanupExpiredSessions $cleanupExpiredSessions,
        private CleanupExpiredPasswordResets $cleanupExpiredPasswordResets,
        private CleanupExpiredMfaChallenges $cleanupExpiredMfaChallenges,
        private CleanupExpiredAuthorizationCodes $cleanupExpiredAuthorizationCodes,
        private CleanupExpiredPasskeyChallenges $cleanupExpiredPasskeyChallenges,
        private ExportAuditEvents $exportAuditEvents
    ) {}

    /**
     * @return array<string, int>
     */
    public function execute() : array
    {
        return [
            'sessions' => $this->cleanupExpiredSessions->execute(),
            'password_resets' => $this->cleanupExpiredPasswordResets->execute(),
            'mfa_challenges' => $this->cleanupExpiredMfaChallenges->execute(),
            'authorization_codes' => $this->cleanupExpiredAuthorizationCodes->execute(),
            'passkey_challenges' => $this->cleanupExpiredPasskeyChallenges->execute(),
            'audit_events_exported' => $this->exportAuditEvents->execute(),
        ];
    }
}
