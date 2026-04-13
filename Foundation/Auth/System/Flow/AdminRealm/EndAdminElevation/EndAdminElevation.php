<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\AdminRealm\EndAdminElevation;

use Avax\Auth\System\Capability\AdminRealm\AdminElevationStoreInterface;
use Avax\Auth\System\Flow\AdminRealm\AdminElevationFailed;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class EndAdminElevation
{
    public function __construct(
        #[SensitiveParameter] private CurrentAuthentication $currentAuthentication,
        private AdminElevationStoreInterface                $elevationStore,
        private AuditLogInterface                           $auditLog,
        private Clock                                       $clock
    ) {}

    /**
     * @throws AdminElevationFailed
     */
    public function execute() : void
    {
        $context = $this->currentAuthentication->read();
        $user    = $context->user();
        $bindingId = $this->bindingId(context: $context);

        if ($user === null || $bindingId === null) {
            throw AdminElevationFailed::notElevated();
        }

        $this->elevationStore->revoke(bindingId: $bindingId);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.admin.elevation.ended',
            occurredAt: $this->clock->now(),
            context   : [
                'user_id' => $user->id,
                'binding_id' => $bindingId,
            ]
        ));
    }

    private function bindingId(AuthenticationContext $context) : string|null
    {
        return $context->sessionId()
            ?? $context->accessTokenId()
            ?? null;
    }
}
