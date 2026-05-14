<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\RequireAdminElevation;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserRole;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\AdminElevationRecord;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\AdminElevationStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\AdminElevationFailed;
use SensitiveParameter;

final readonly class RequireAdminElevation
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication        $currentAuthentication,
        private AdminElevationStoreInterface $adminElevationStore,
        private Clock                        $clock,
    ) {}

    /**
     * @throws AdminElevationFailed
     */
    public function execute() : void
    {
        $authenticationContext = $this->currentAuthentication->read();
        $user                  = $authenticationContext->user();
        $bindingId             = $this->bindingId(context: $authenticationContext);

        if (! $user instanceof AuthenticatedUser) {
            throw AdminElevationFailed::unauthenticated();
        }

        if (! $user->hasRole(role: UserRole::ADMIN)) {
            throw AdminElevationFailed::forbidden();
        }

        if ($bindingId === null) {
            throw AdminElevationFailed::missingBinding();
        }

        $record = $this->adminElevationStore->find(bindingId: $bindingId);

        if (! $record instanceof AdminElevationRecord || $record->userId !== $user->id || $record->isExpiredAt(moment: $this->clock->now())) {
            $this->adminElevationStore->revoke(bindingId: $bindingId);

            throw AdminElevationFailed::notElevated();
        }
    }

    private function bindingId(AuthenticationContext $authenticationContext) : string|null
    {
        return $authenticationContext->sessionId()
            ?? $authenticationContext->accessTokenId()
            ?? null;
    }
}
