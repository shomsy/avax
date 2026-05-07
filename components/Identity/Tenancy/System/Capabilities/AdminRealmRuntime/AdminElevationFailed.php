<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime;

use RuntimeException;

final class AdminElevationFailed extends RuntimeException
{
    public static function unauthenticated() : self
    {
        return new self(message: 'Authentication is required.');
    }

    public static function forbidden() : self
    {
        return new self(message: 'Admin elevation requires an administrator.');
    }

    public static function notElevated() : self
    {
        return new self(message: 'Admin elevation is required.');
    }

    public static function missingBinding() : self
    {
        return new self(message: 'Current authentication cannot be elevated.');
    }

    public static function phishingResistantRequired() : self
    {
        return new self(message: 'Admin elevation requires phishing-resistant authentication.');
    }
}
