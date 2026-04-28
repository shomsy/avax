<?php

declare(strict_types=1);

namespace Avax\Components\Auth\Examples\Jobs;

use Avax\Components\Auth\System\Capabilities\Diagnostics\Audit\ExportAuditEvents\ExportAuditEvents;
use Avax\Components\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\CleanupExpiredAuthorizationCodes\CleanupExpiredAuthorizationCodes;
use Avax\Components\Auth\System\Capabilities\Identity\Mfa\Runtime\Limit\CleanupExpiredMfaChallenges;
use Avax\Components\Auth\System\Capabilities\Identity\Passkey\Runtime\CleanupExpiredPasskeyChallenges\CleanupExpiredPasskeyChallenges;
use Avax\Components\Auth\System\Capabilities\Identity\Sessions\Runtime\CleanupExpiredSessions\CleanupExpiredSessions;
use Avax\Components\Auth\System\Flows\RecoverAccess\PasswordReset\CleanupExpiredPasswordResets\CleanupExpiredPasswordResets;
use SensitiveParameter;

/**
 * Reference scheduler entrypoint for auth maintenance and export jobs.
 */
final readonly class RunAuthMaintenanceJobs
{
    public function __construct(
        #[SensitiveParameter] private CleanupExpiredSessions           $cleanupExpiredSessions,
        #[SensitiveParameter] private CleanupExpiredPasswordResets     $cleanupExpiredPasswordResets,
        private CleanupExpiredMfaChallenges                            $cleanupExpiredMfaChallenges,
        #[SensitiveParameter] private CleanupExpiredAuthorizationCodes $cleanupExpiredAuthorizationCodes,
        private CleanupExpiredPasskeyChallenges                        $cleanupExpiredPasskeyChallenges,
        private ExportAuditEvents                                      $exportAuditEvents
    ) {}

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
