<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Logout;

use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;

/**
 * High-level orchestrator for the logout process.
 *
 * Banal: The Logout file.
 */
final readonly class Logout
{
    public function __construct(
        private IdentityInterface               $identity,
        private CurrentAuthentication           $currentAuthentication,
        private AuditLogInterface               $auditLog,
        private RefreshTokenStoreInterface|null $refreshTokenStore = null
    ) {}

    public function execute() : void
    {
        $context = $this->currentAuthentication->read();
        $user    = $context->user();

        if ($user !== null) {
            $this->refreshTokenStore?->revokeUser(new UserId($user->id));
            $this->auditLog->record(new AuditEvent(
                                        name      : 'auth.logout.succeeded',
                                        occurredAt: new \DateTimeImmutable(),
                                        context   : [
                                                        'user_id' => $user->id,
                                                        'mode'    => $context->mode()->value,
                                                    ]
                                    ));
        }

        $this->identity->clear($context);
        $this->currentAuthentication->clear();
    }
}
