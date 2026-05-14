<?php

declare(strict_types=1);

namespace Avax\Components\Security\Cryptography\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Security\Cryptography\System\Capabilities\Encryption\Encrypter;
use Avax\Components\Security\Cryptography\System\Capabilities\Encryption\EncrypterInterface;
use Avax\Components\Security\Cryptography\System\Capabilities\HealthCheck\CheckCryptographyHealth;

/**
 * CryptographyServiceProvider — registers cryptography component dependencies.
 */
final class CryptographyServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Encrypter — AES-256-GCM encryption (key passed per-call via EncryptionKey)
        $container->singleton(EncrypterInterface::class, static fn () : EncrypterInterface => new Encrypter());

        // Concrete alias
        $container->singleton(Encrypter::class, static fn (ContainerInterface $c) : Encrypter => $c->get(EncrypterInterface::class));

        // Health check
        $container->singleton(CheckCryptographyHealth::class, static fn () : CheckCryptographyHealth => new CheckCryptographyHealth());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
