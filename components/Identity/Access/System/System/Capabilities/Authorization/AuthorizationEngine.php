<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\System\Capabilities\Authorization;

final class AuthorizationEngine
{
    /** @var array<string, true> */
    private array $permissions = [];

    /**
     * @param iterable<string> $permissions
     */
    public function __construct(iterable $permissions = [], private readonly bool $defaultAllow = false)
    {
        foreach ($permissions as $permission) {
            $this->grant(permission: $permission);
        }
    }

    public function grant(string $permission) : void
    {
        if ($permission !== '') {
            $this->permissions[$permission] = true;
        }
    }

    public function revoke(string $permission) : void
    {
        unset($this->permissions[$permission]);
    }

    public function check(string $permission, mixed $resource = null) : bool
    {
        if ($permission === '') {
            return false;
        }

        if (isset($this->permissions[$permission])) {
            return true;
        }

        if (isset($this->permissions['*'])) {
            return true;
        }

        if ($resource !== null) {
            $resourcePermission = $permission . ':' . $this->resourceKey(resource: $resource);

            if (isset($this->permissions[$resourcePermission])) {
                return true;
            }
        }

        return $this->defaultAllow;
    }

    private function resourceKey(mixed $resource) : string
    {
        if (is_scalar(value: $resource) || $resource === null) {
            return (string) $resource;
        }

        if (is_object(value: $resource)) {
            return $resource::class;
        }

        return md5(serialize(value: $resource));
    }
}
