<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Flows\AuthorizeAction;

use Avax\Components\Identity\Capabilities\Audit\IdentityAuditTrail;
use Avax\Components\Identity\Capabilities\Audit\IdentityEvent;
use Avax\Components\Identity\Capabilities\Permissions\DecidePermission;
use Avax\Components\Identity\Foundation\Time\Clock;

final readonly class AuthorizeAction
{
    public function __construct(private DecidePermission $permissions, private IdentityAuditTrail $audit, private Clock $clock) {}

    public function authorize(AuthorizationRequest $request): AuthorizationResult
    {
        $allowed = $this->permissions->isAllowed($request->userId(), $request->permission(), $request->tenantId());
        $this->audit->record(new IdentityEvent(
            $allowed ? 'identity.authorization.allowed' : 'identity.authorization.denied',
            $this->clock->now(),
            ['user_id' => $request->userId()->toString(), 'permission' => $request->permission()->key()],
        ));

        return $allowed ? AuthorizationResult::allowed() : AuthorizationResult::denied();
    }
}
