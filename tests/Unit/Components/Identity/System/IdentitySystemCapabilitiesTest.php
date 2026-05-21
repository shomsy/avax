<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\System;

use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use Avax\Components\Identity\Access\System\PublicSurface\Access;
use Avax\Components\Identity\Auth\System\PublicSurface\Auth;
use Avax\Components\Identity\Credentials\System\PublicSurface\Credentials;
use Avax\Components\Identity\ExternalIdentity\System\PublicSurface\ExternalIdentity;
use Avax\Components\Identity\Risk\System\PublicSurface\Risk;
use Avax\Components\Identity\System\Capabilities\IdentityRuntime\IdentityRuntime;
use Avax\Components\Identity\System\Configuration\Builders\IdentityRuntime as BuildIdentityRuntime;
use Avax\Components\Identity\System\Configuration\IdentityConfiguration;
use Avax\Components\Identity\System\Configuration\IdentityServiceProvider;
use Avax\Components\Identity\System\PublicSurface\Identity;
use Avax\Components\Identity\Tenancy\System\PublicSurface\Tenancy;
use Avax\Components\Identity\Tokens\System\PublicSurface\Tokens;
use RuntimeException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IdentitySystemCapabilitiesTest extends TestCase
{
    private string|null $previousTokenSecret = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousTokenSecret = $_ENV['TOKEN_SECRET'] ?? null;
        $_ENV['TOKEN_SECRET'] = 'identity-test-secret';
    }

    protected function tearDown(): void
    {
        if ($this->previousTokenSecret === null) {
            unset($_ENV['TOKEN_SECRET']);
        } else {
            $_ENV['TOKEN_SECRET'] = $this->previousTokenSecret;
        }

        parent::tearDown();
    }

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

    #[Test]
    public function defaultRuntimeAssemblesEveryRootIdentitySurface(): void
    {
        $runtime = BuildIdentityRuntime::defaults()->runtime();

        self::assertInstanceOf(Auth::class, $runtime->auth());
        self::assertInstanceOf(Access::class, $runtime->access());
        self::assertInstanceOf(Credentials::class, $runtime->credentials());
        self::assertInstanceOf(Tokens::class, $runtime->tokens());
        self::assertInstanceOf(Tenancy::class, $runtime->tenancy());
        self::assertInstanceOf(Risk::class, $runtime->risk());
        self::assertInstanceOf(ExternalIdentity::class, $runtime->externalIdentity());
    }

    #[Test]
    public function defaultRuntimeFailsClosedWithoutTokenSecret(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('TOKEN_SECRET');

        BuildIdentityRuntime::defaults(new IdentityConfiguration())->runtime();
    }

    #[Test]
    public function identityServiceProviderRegistersConfigurationAndRuntimeAssembly(): void
    {
        $container = new SimpleContainer();

        (new IdentityServiceProvider())->register($container);

        self::assertInstanceOf(IdentityConfiguration::class, $container->get(IdentityConfiguration::class));
        self::assertInstanceOf(BuildIdentityRuntime::class, $container->get(BuildIdentityRuntime::class));
        self::assertInstanceOf(IdentityRuntime::class, $container->get(IdentityRuntime::class));
    }
}
