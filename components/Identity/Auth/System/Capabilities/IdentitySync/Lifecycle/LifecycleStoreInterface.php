<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;

interface LifecycleStoreInterface
{
    public function save(LifecycleRecord $lifecycleRecord) : void;

    public function find(UserId $userId) : LifecycleRecord|null;
}
