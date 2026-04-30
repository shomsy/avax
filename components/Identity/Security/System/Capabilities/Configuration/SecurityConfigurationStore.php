<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Capabilities\Configuration;

/**
 * SecurityConfigurationStore - Persistence for security settings.
 */
final readonly class SecurityConfigurationStore
{
    public function read(string $tenantId) : object
    {
        return (object)[
            'mfa_required'    => true,
            'password_policy' => 'enterprise_strict',
        ];
    }
}
