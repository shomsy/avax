<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Logout;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * High-level orchestrator for the logout process.
 *
 * Banal: The Logout file.
 */
final readonly class Logout
{
    private RefreshTokenStoreInterface|null $refreshTokenStore;
    private SessionRegistryInterface|null   $sessionRegistry;
    private Clock                           $clock;
    private AuditLogInterface               $auditLog;
    private CurrentAuthentication           $currentAuthentication;
    private IdentityInterface               $identity;

    public function __construct(
        IdentityInterface                                     $identity,
        #[SensitiveParameter] CurrentAuthentication           $currentAuthentication,
        AuditLogInterface                                     $auditLog,
        Clock                                                 $clock,
        #[SensitiveParameter] SessionRegistryInterface|null   $sessionRegistry = null,
        #[SensitiveParameter] RefreshTokenStoreInterface|null $refreshTokenStore = null
    )
    {
        $this->identity              = $identity;
        $this->currentAuthentication = $currentAuthentication;
        $this->auditLog              = $auditLog;
        $this->clock                 = $clock;
        $this->sessionRegistry       = $sessionRegistry;
        $this->refreshTokenStore     = $refreshTokenStore;
    }

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
                                                               'user_id'    => $user->id,
                                                               'mode'       => $context->mode()->value,
                                                               'session_id' => $context->sessionId(),
                                                           ]
                                           ));
        }

        $this->identity->clear(context: $context);
        $this->currentAuthentication->clear();
    }
}
