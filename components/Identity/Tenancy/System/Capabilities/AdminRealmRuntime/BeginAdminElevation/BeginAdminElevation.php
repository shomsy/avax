<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\BeginAdminElevation;

use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserRole;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\AdminElevationRecord;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\AdminElevationStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\AdminElevation;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\AdminElevationFailed;
use DateMalformedStringException;
use SensitiveParameter;

final readonly class BeginAdminElevation
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication $currentAuthentication,
        private RequireFreshMfa $requireFreshMfa,
        private AdminElevationStoreInterface $elevationStore,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        private bool $phishingResistantRequired = false,
    ) {}

    /**
     * @throws AdminElevationFailed
     * @throws DateMalformedStringException
     * @throws Unauthenticated
     */
    public function execute(): AdminElevation
    {
        $context = $this->currentAuthentication->read();
        $user = $context->user();

        if ($user === null) {
            throw AdminElevationFailed::unauthenticated();
        }

        if (! $user->hasRole(role: UserRole::ADMIN)) {
            throw AdminElevationFailed::forbidden();
        }

        if ($this->phishingResistantRequired && ! $context->isPhishingResistant()) {
            throw AdminElevationFailed::phishingResistantRequired();
        }

        $this->requireFreshMfa->execute();

        $bindingId = $this->bindingId(context: $context);

        if ($bindingId === null) {
            throw AdminElevationFailed::missingBinding();
        }

        $expiresAt = $this->clock->now()->modify(modifier: '+15 minutes');
        $this->elevationStore->start(record: new AdminElevationRecord(
            userId   : $user->id,
            bindingId: $bindingId,
            expiresAt: $expiresAt,
        ));

        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.admin.elevation.started',
            occurredAt: $this->clock->now(),
            context   : [
                            'user_id' => $user->id,
                'binding_id' => $bindingId,
            ],
        ));

        return new AdminElevation(
            bindingId: $bindingId,
            expiresAt: $expiresAt,
        );
    }

    private function bindingId(AuthenticationContext $context): ?string
    {
        return $context->sessionId()
            ?? $context->accessTokenId()
            ?? null;
    }
}
