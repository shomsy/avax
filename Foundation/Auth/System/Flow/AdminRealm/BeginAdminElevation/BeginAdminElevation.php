<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\AdminRealm\BeginAdminElevation;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
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
use DateMalformedStringException;
use SensitiveParameter;

final readonly class BeginAdminElevation
{
    private bool                         $phishingResistantRequired;
    private Clock                        $clock;
    private AuditLogInterface            $auditLog;
    private AdminElevationStoreInterface $elevationStore;
    private RequireFreshMfa              $requireFreshMfa;
    private CurrentAuthentication        $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication $currentAuthentication,
        RequireFreshMfa                             $requireFreshMfa,
        AdminElevationStoreInterface                $elevationStore,
        AuditLogInterface                           $auditLog,
        Clock                                       $clock,
        bool                                        $phishingResistantRequired = false
    )
    {
        $this->currentAuthentication     = $currentAuthentication;
        $this->requireFreshMfa           = $requireFreshMfa;
        $this->elevationStore            = $elevationStore;
        $this->auditLog                  = $auditLog;
        $this->clock                     = $clock;
        $this->phishingResistantRequired = $phishingResistantRequired;
    }

    /**
     * @throws AdminElevationFailed
     * @throws DateMalformedStringException
     * @throws Unauthenticated
     */
    public function execute() : AdminElevation
    {
        $context = $this->currentAuthentication->read();
        $user    = $context->user();

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
                                                 expiresAt: $expiresAt
                                             ));

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.admin.elevation.started',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'user_id'    => $user->id,
                                                           'binding_id' => $bindingId,
                                                       ]
                                       ));

        return new AdminElevation(
            bindingId: $bindingId,
            expiresAt: $expiresAt
        );
    }

    private function bindingId(AuthenticationContext $context) : string|null
    {
        return $context->sessionId()
            ?? $context->accessTokenId()
            ?? null;
    }
}
