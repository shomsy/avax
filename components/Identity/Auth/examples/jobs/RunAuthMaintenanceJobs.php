<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Examples\Jobs;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\ExportAuditEvents\ExportAuditEvents;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\CleanupExpiredSessions\CleanupExpiredSessions;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\CleanupExpiredPasswordResets\CleanupExpiredPasswordResets;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Limit\CleanupExpiredMfaChallenges;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\CleanupExpiredPasskeyChallenges\CleanupExpiredPasskeyChallenges;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\CleanupExpiredAuthorizationCodes\CleanupExpiredAuthorizationCodes;
use SensitiveParameter;

/**
 * Reference scheduler entrypoint for auth maintenance and export jobs.
 */
final readonly class RunAuthMaintenanceJobs
{
    public function __construct(
        #[SensitiveParameter]
        private CleanupExpiredSessions $cleanupExpiredSessions,
        #[SensitiveParameter]
        private CleanupExpiredPasswordResets $cleanupExpiredPasswordResets,
        private CleanupExpiredMfaChallenges $cleanupExpiredMfaChallenges,
        #[SensitiveParameter]
        private CleanupExpiredAuthorizationCodes $cleanupExpiredAuthorizationCodes,
        private CleanupExpiredPasskeyChallenges $cleanupExpiredPasskeyChallenges,
        private ExportAuditEvents $exportAuditEvents,
    ) {}

    /**
     * @return array<string, int>
     */
    public function execute(): array
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
