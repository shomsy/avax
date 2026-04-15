<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\AdminRealm\RequireAdminElevation;

use Avax\Auth\System\Capability\AdminRealm\AdminElevationStoreInterface;
use Avax\Auth\System\Capability\User\UserRole;
use Avax\Auth\System\Flow\AdminRealm\AdminElevationFailed;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class RequireAdminElevation
{
    private Clock                        $clock;
    private AdminElevationStoreInterface $elevationStore;
    private CurrentAuthentication        $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication $currentAuthentication,
        AdminElevationStoreInterface                $elevationStore,
        Clock                                       $clock
    )
    {
        $this->currentAuthentication = $currentAuthentication;
        $this->elevationStore        = $elevationStore;
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

        if ($user === null) {
            throw AdminElevationFailed::unauthenticated();
        }

        if (! $user->hasRole(role: UserRole::ADMIN)) {
            throw AdminElevationFailed::forbidden();
        }

        if ($bindingId === null) {
            throw AdminElevationFailed::missingBinding();
        }

        $record = $this->elevationStore->find(bindingId: $bindingId);

        if ($record === null || $record->userId !== $user->id || $record->isExpiredAt(moment: $this->clock->now())) {
            $this->elevationStore->revoke(bindingId: $bindingId);
            throw AdminElevationFailed::notElevated();
        }
    }

    private function bindingId(AuthenticationContext $context) : string|null
    {
        return $context->sessionId()
            ?? $context->accessTokenId()
            ?? null;
    }
}
