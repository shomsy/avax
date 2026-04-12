<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\AdminRealm\BeginAdminElevation;

use Avax\Auth\System\Capability\AdminRealm\AdminElevationRecord;
use Avax\Auth\System\Capability\AdminRealm\AdminElevationStoreInterface;
use Avax\Auth\System\Capability\User\UserRole;
use Avax\Auth\System\Flow\AdminRealm\AdminElevation;
use Avax\Auth\System\Flow\AdminRealm\AdminElevationFailed;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Mfa\StepUp\RequireFreshMfa;
use Avax\Auth\System\Foundation\Clock;

final readonly class BeginAdminElevation
{
    public function __construct(
        private CurrentAuthentication $currentAuthentication,
        private RequireFreshMfa $requireFreshMfa,
        private AdminElevationStoreInterface $elevationStore,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * @throws AdminElevationFailed
     */
    public function execute() : AdminElevation
    {
        $context = $this->currentAuthentication->read();
        $user    = $context->user();

        if ($user === null) {
            throw AdminElevationFailed::unauthenticated();
        }

        if (! $user->hasRole(UserRole::ADMIN)) {
            throw AdminElevationFailed::forbidden();
        }

        $this->requireFreshMfa->execute();

        $bindingId = $this->bindingId($context);

        if ($bindingId === null) {
            throw AdminElevationFailed::missingBinding();
        }

        $expiresAt = $this->clock->now()->modify('+15 minutes');
        $this->elevationStore->start(new AdminElevationRecord(
            userId    : $user->id,
            bindingId : $bindingId,
            expiresAt : $expiresAt
        ));

        $this->auditLog->record(new AuditEvent(
            name      : 'auth.admin.elevation.started',
            occurredAt: $this->clock->now(),
            context   : [
                'user_id' => $user->id,
                'binding_id' => $bindingId,
            ]
        ));

        return new AdminElevation(
            bindingId : $bindingId,
            expiresAt : $expiresAt
        );
    }

    private function bindingId(AuthenticationContext $context) : string|null
    {
        return $context->sessionId()
            ?? $context->accessTokenId()
            ?? null;
    }
}
