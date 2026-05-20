<?php

declare(strict_types=1);

namespace Avax\Components\Application\Localization\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Application\Localization\System\Capabilities\TranslationLoading\ArrayTranslationLoader;
use Avax\Components\Application\Localization\System\Capabilities\TranslationLoading\TranslationLoaderInterface;
use Avax\Components\Application\Localization\System\PublicSurface\Translator;
use Avax\Components\Application\Localization\System\PublicSurface\TranslatorInterface;

/**
 * LocalizationServiceProvider — registers Application/Localization component dependencies.
 *
 * Registers the translation loader, binds the loader interface, and configures
 * the Translator with default locale and fallback.
 */
final class LocalizationServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // ArrayTranslationLoader — in-memory translation loader with no external dependencies
        $container->singleton(ArrayTranslationLoader::class, static fn () : ArrayTranslationLoader => new ArrayTranslationLoader());

        // TranslationLoaderInterface -> ArrayTranslationLoader
        $container->singleton(TranslationLoaderInterface::class, static fn (ContainerInterface $c) : TranslationLoaderInterface => $c->get(ArrayTranslationLoader::class));

        // Translator — needs TranslationLoaderInterface, locale='en', fallback='en'
        $container->singleton(Translator::class, static fn (ContainerInterface $c) : Translator => new Translator(
            loader  : $c->get(TranslationLoaderInterface::class),
            locale  : 'en',
            fallback: 'en',
        ));

        // TranslatorInterface -> Translator
        $container->singleton(TranslatorInterface::class, static fn (ContainerInterface $c) : TranslatorInterface => $c->get(Translator::class));
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
