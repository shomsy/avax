<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim;

use RuntimeException;

final class ScimFailed extends RuntimeException
{
    public static function invalidDirectoryToken() : self
    {
        return new self('Invalid SCIM directory token.');
    }

    public static function invalidGroupRoleMapping() : self
    {
        return new self('SCIM group-to-role mapping is invalid.');
    }

    public static function unknownDirectory() : self
    {
        return new self('SCIM directory is not registered.');
    }

    public static function unknownProvisionedIdentity() : self
    {
        return new self('SCIM provisioned identity was not found.');
    }
}
