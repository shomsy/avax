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
    private Clock                        $clock;
    private AuditLogInterface            $auditLog;
    private AdminElevationStoreInterface $elevationStore;
    private CurrentAuthentication        $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication $currentAuthentication,
        AdminElevationStoreInterface                $elevationStore,
        AuditLogInterface                           $auditLog,
        Clock                                       $clock
    )
    {
        $this->currentAuthentication = $currentAuthentication;
        $this->elevationStore        = $elevationStore;
        $this->auditLog              = $auditLog;
        $this->clock                 = $clock;
    }

    /**
     * @throws AdminElevationFailed
     */
    public function execute() : void
    {
        $context   = $this->currentAuthentication->read();
        $user      = $context->user();
        $bindingId = $this->bindingId(context: $context);

        if ($user === null || $bindingId === null) {
            throw AdminElevationFailed::notElevated();
        }

        $this->elevationStore->revoke(bindingId: $bindingId);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.admin.elevation.ended',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'user_id'    => $user->id,
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
