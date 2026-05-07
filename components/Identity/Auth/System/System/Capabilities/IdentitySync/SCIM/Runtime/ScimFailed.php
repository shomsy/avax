<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\SCIM\Runtime;

use RuntimeException;

final class ScimFailed extends RuntimeException
{
    public static function invalidDirectoryToken() : self
    {
        return new self(message: 'Invalid SCIM directory token.');
    }

    public static function invalidGroupRoleMapping() : self
    {
        return new self(message: 'SCIM group-to-role mapping is invalid.');
    }

    public static function unknownDirectory() : self
    {
        return new self(message: 'SCIM directory is not registered.');
    }

    public static function unknownProvisionedIdentity() : self
    {
        return new self(message: 'SCIM provisioned identity was not found.');
    }

    public static function invalidBulkRequest(string $message) : self
    {
        return new self(message: $message);
    }

    public static function tooManyBulkOperations(int $provided, int $maximum) : self
    {
        return new self(message: sprintf('Too many bulk operations: %d. Maximum allowed: %d', $provided, $maximum));
    }

    public static function serviceUnavailable(string $message = 'SCIM directory is temporarily unavailable.') : self
    {
        return new self(message: $message);
    }

    public static function throttled(int $retryAfterSeconds, string $scope) : self
    {
        return new self(message: sprintf('SCIM directory operation throttled for %s. Retry after %d seconds.', $scope, $retryAfterSeconds));
    }
}
