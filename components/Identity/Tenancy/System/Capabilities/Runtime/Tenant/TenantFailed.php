<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant;

use RuntimeException;

final class TenantFailed extends RuntimeException
{
    public static function tenantNotFound(string $tenantSlug) : self
    {
        return new self(message: "Tenant [{$tenantSlug}] was not found.");
    }

    public static function tenantSlugTaken(string $tenantSlug) : self
    {
        return new self(message: "Tenant slug [{$tenantSlug}] is already taken.");
    }

    public static function userNotFound(int $userId) : self
    {
        return new self(message: "Tenant user [{$userId}] was not found.");
    }

    public static function inviteNotFound() : self
    {
        return new self(message: 'Tenant invite was not found.');
    }

    public static function inviteEmailMismatch() : self
    {
        return new self(message: 'Tenant invite email does not match the accepting user.');
    }

    public static function memberAlreadyExists() : self
    {
        return new self(message: 'Tenant member already exists.');
    }

    public static function memberNotFound() : self
    {
        return new self(message: 'Tenant member was not found.');
    }

    public static function ownerCannotBeRemoved() : self
    {
        return new self(message: 'Tenant owner cannot be removed.');
    }
}
