<?php

declare(strict_types=1);

namespace Avax\Auth\Examples\Jobs;

use Avax\Auth\System\Flows\Diagnostics\ExportAuditEvents\ExportAuditEvents;
use Avax\Auth\System\Flows\Mfa\Challenge\CleanupExpiredMfaChallenges\CleanupExpiredMfaChallenges;
use Avax\Auth\System\Flows\OAuth\CleanupExpiredAuthorizationCodes\CleanupExpiredAuthorizationCodes;
use Avax\Auth\System\Flows\Passkey\CleanupExpiredPasskeyChallenges\CleanupExpiredPasskeyChallenges;
use Avax\Auth\System\Flows\Recover\CleanupExpiredPasswordResets\CleanupExpiredPasswordResets;
use Avax\Auth\System\Flows\Session\CleanupExpiredSessions\CleanupExpiredSessions;
use SensitiveParameter;

/**
 * Reference scheduler entrypoint for auth maintenance and export jobs.
 */
final readonly class RunAuthMaintenanceJobs
{
    private ExportAuditEvents                $exportAuditEvents;
    private CleanupExpiredPasskeyChallenges  $cleanupExpiredPasskeyChallenges;
    private CleanupExpiredAuthorizationCodes $cleanupExpiredAuthorizationCodes;
    private CleanupExpiredMfaChallenges      $cleanupExpiredMfaChallenges;
    private CleanupExpiredPasswordResets     $cleanupExpiredPasswordResets;
    private CleanupExpiredSessions           $cleanupExpiredSessions;

    public function __construct(
        #[SensitiveParameter] CleanupExpiredSessions           $cleanupExpiredSessions,
        #[SensitiveParameter] CleanupExpiredPasswordResets     $cleanupExpiredPasswordResets,
        CleanupExpiredMfaChallenges                            $cleanupExpiredMfaChallenges,
        #[SensitiveParameter] CleanupExpiredAuthorizationCodes $cleanupExpiredAuthorizationCodes,
        CleanupExpiredPasskeyChallenges                        $cleanupExpiredPasskeyChallenges,
        ExportAuditEvents                                      $exportAuditEvents
    )
    {
        $this->cleanupExpiredSessions           = $cleanupExpiredSessions;
        $this->cleanupExpiredPasswordResets     = $cleanupExpiredPasswordResets;
        $this->cleanupExpiredMfaChallenges      = $cleanupExpiredMfaChallenges;
        $this->cleanupExpiredAuthorizationCodes = $cleanupExpiredAuthorizationCodes;
        $this->cleanupExpiredPasskeyChallenges  = $cleanupExpiredPasskeyChallenges;
        $this->exportAuditEvents                = $exportAuditEvents;
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
