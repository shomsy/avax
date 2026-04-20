<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity;

use RuntimeException;

final class TenantSecurityFailed extends RuntimeException
{
    public static function unknownChangeRequest() : self
    {
        return new self(message: 'Tenant security change request was not found.');
    }

    public static function approvalRequired() : self
    {
        return new self(message: 'Tenant security approval is required before apply.');
    }

    public static function unknownFederationConnection() : self
    {
        return new self(message: 'Tenant security references an unknown federation connection.');
    }

    public static function unknownScimDirectory() : self
    {
        return new self(message: 'Tenant security references an unknown SCIM directory.');
    }
}
