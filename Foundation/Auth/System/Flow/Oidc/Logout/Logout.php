<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\Logout;

use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\Session\SessionRegistryInterface;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Foundation\Clock;

/**
 * High-level orchestrator for the OIDC logout process.
 *
 * Banal: The Oidc Logout file.
 */
final readonly class Logout
{
    public function __construct(
        private IdentityInterface               $identity,
        private CurrentAuthentication           $currentAuthentication,
        private AuditLogInterface               $auditLog,
        private Clock                           $clock,
        private SessionRegistryInterface|null   $sessionRegistry = null,
        private RefreshTokenStoreInterface|null $refreshTokenStore = null
    ) {}

    public function execute() : void
    {
        $context = $this->currentAuthentication->read();
        $user    = $context->user();
        $now     = $this->clock->now();

        if ($user !== null) {
            if ($context->sessionId() !== null) {
                $this->sessionRegistry?->revoke($context->sessionId(), $now, 'logout');
            }

            $this->refreshTokenStore?->revokeUser(new UserId($user->id));
            $this->auditLog->record(new AuditEvent(
                name      : 'auth.oidc.logout.succeeded',
                occurredAt: $now,
                context   : [
                    'user_id' => $user->id,
                    'mode'    => $context->mode()->value,
                    'session_id' => $context->sessionId(),
                ]
            ));
        }

        $this->identity->clear($context);
        $this->currentAuthentication->clear();
    }
}