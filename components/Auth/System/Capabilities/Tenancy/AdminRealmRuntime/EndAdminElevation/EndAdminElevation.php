<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\EndAdminElevation;

use components\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use components\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use components\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\AdminElevationFailed;
use components\Auth\System\Capabilities\Tenancy\AdminRealmSupport\AdminElevationStoreInterface;
use components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use components\Auth\System\Foundation\Clock;
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
