<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Localization\Configuration;

use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use Avax\Components\Application\Localization\System\Capabilities\TranslationLoading\ArrayTranslationLoader;
use Avax\Components\Application\Localization\System\Capabilities\TranslationLoading\TranslationLoaderInterface;
use Avax\Components\Application\Localization\System\Configuration\LocalizationServiceProvider;
use Avax\Components\Application\Localization\System\PublicSurface\Translator;
use Avax\Components\Application\Localization\System\PublicSurface\TranslatorInterface;
use PHPUnit\Framework\TestCase;

final class LocalizationServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private LocalizationServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new LocalizationServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_array_translation_loader_resolves(): void
    {
        $loader = $this->container->get(ArrayTranslationLoader::class);

        $this->assertInstanceOf(ArrayTranslationLoader::class, $loader);
    }

    public function test_translation_loader_interface_resolves(): void
    {
        $loader = $this->container->get(TranslationLoaderInterface::class);

        $this->assertInstanceOf(TranslationLoaderInterface::class, $loader);
        $this->assertInstanceOf(ArrayTranslationLoader::class, $loader);
    }

    public function test_translator_resolves(): void
    {
        $translator = $this->container->get(Translator::class);

        $this->assertInstanceOf(Translator::class, $translator);
    }

    public function test_translator_interface_resolves(): void
    {
        $translator = $this->container->get(TranslatorInterface::class);

        $this->assertInstanceOf(TranslatorInterface::class, $translator);
        $this->assertInstanceOf(Translator::class, $translator);
    }
}
