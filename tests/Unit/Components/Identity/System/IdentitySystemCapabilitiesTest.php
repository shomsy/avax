<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\System;

use Avax\Components\Identity\Credentials\System\PublicSurface\Credentials;
use Avax\Components\Identity\ExternalIdentity\System\PublicSurface\ExternalIdentity;
use Avax\Components\Identity\System\PublicSurface\Identity;
use Avax\Components\Identity\Tenancy\System\PublicSurface\Tenancy;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IdentitySystemCapabilitiesTest extends TestCase
{
    #[Test]
    public function tenancyReturnsTenancySurface(): void
    {
        $tenancy = Identity::tenancy();
        self::assertInstanceOf(Tenancy::class, $tenancy);
    }

    #[Test]
    public function credentialsReturnsCredentialsSurface(): void
    {
        $credentials = Identity::credentials();
        self::assertInstanceOf(Credentials::class, $credentials);
    }

    #[Test]
    public function externalIdentityReturnsExternalIdentitySurface(): void
    {
        $externalIdentity = Identity::externalIdentity();
        self::assertInstanceOf(ExternalIdentity::class, $externalIdentity);
    }
}
