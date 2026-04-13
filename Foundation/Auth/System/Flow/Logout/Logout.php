<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Logout;

use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\Session\SessionRegistryInterface;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * High-level orchestrator for the logout process.
 *
 * Banal: The Logout file.
 */
final readonly class Logout
{
    public function __construct(
        private IdentityInterface                                     $identity,
        #[SensitiveParameter] private CurrentAuthentication           $currentAuthentication,
        private AuditLogInterface                                     $auditLog,
        private Clock                                                 $clock,
        #[SensitiveParameter] private SessionRegistryInterface|null   $sessionRegistry = null,
        #[SensitiveParameter] private RefreshTokenStoreInterface|null $refreshTokenStore = null
    ) {}

    public function execute() : void
    {
        $context = $this->currentAuthentication->read();
        $user    = $context->user();
        $now     = $this->clock->now();

        if ($user !== null) {
            if ($context->sessionId() !== null) {
                $this->sessionRegistry?->revoke(sessionId: $context->sessionId(), revokedAt: $now, reason: 'logout');
            }

            if ($context->refreshTokenFamilyId() !== null) {
                $this->refreshTokenStore?->revokeFamily(familyId: $context->refreshTokenFamilyId());
            }

            $this->auditLog->record(event: new AuditEvent(
                                        name      : 'auth.logout.succeeded',
                                        occurredAt: $now,
                                        context   : [
                                                        'user_id' => $user->id,
                                                        'mode'    => $context->mode()->value,
                                                        'session_id' => $context->sessionId(),
                                                    ]
                                    ));
        }

        $this->identity->clear(context: $context);
        $this->currentAuthentication->clear();
    }
}
