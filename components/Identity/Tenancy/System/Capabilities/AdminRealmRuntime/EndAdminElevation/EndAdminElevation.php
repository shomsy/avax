<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\EndAdminElevation;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\AdminElevationStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\AdminElevationFailed;
use SensitiveParameter;

final readonly class EndAdminElevation
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication $currentAuthentication,
        private AdminElevationStoreInterface $adminElevationStore,
        private AuditLogInterface $auditLog,
        private Clock $clock,
    ) {
    }

    /**
     * @throws AdminElevationFailed
     */
    public function execute(): void
    {
        $authenticationContext = $this->currentAuthentication->read();
        $user = $authenticationContext->user();
        $bindingId = $this->bindingId(context: $authenticationContext);

        if (! $user instanceof AuthenticatedUser || $bindingId === null) {
            throw AdminElevationFailed::notElevated();
        }

        $this->adminElevationStore->revoke(bindingId: $bindingId);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.admin.elevation.ended',
            occurredAt: $this->clock->now(),
            context   : [
                'user_id' => $user->id,
                'binding_id' => $bindingId,
            ],
        ));
    }

    private function bindingId(AuthenticationContext $authenticationContext): ?string
    {
        return $authenticationContext->sessionId()
            ?? $authenticationContext->accessTokenId()
            ?? null;
    }
}
