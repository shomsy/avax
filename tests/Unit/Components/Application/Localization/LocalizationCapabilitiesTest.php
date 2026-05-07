<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Localization;

use Avax\Components\Application\Localization\System\Capabilities\TranslationLoading\ArrayTranslationLoader;
use Avax\Components\Application\Localization\System\PublicSurface\Translator;
use PHPUnit\Framework\TestCase;

final class LocalizationCapabilitiesTest extends TestCase
{
    private ArrayTranslationLoader $loader;
    private Translator             $translator;

    public function test_it_can_translate_simple_key() : void
    {
        $this->loader->addMessages('en', 'messages', [
            'welcome' => 'Welcome to AvaX',
        ]);

        $this->assertSame('Welcome to AvaX', $this->translator->get('messages.welcome'));
    }

    public function test_it_returns_key_when_translation_missing() : void
    {
        $this->assertSame('messages.missing', $this->translator->get('messages.missing'));
    }

    public function test_it_can_replace_parameters() : void
    {
        $this->loader->addMessages('en', 'messages', [
            'greet' => 'Hello :name!',
        ]);

        $this->assertSame('Hello Antigravity!', $this->translator->get('messages.greet', ['name' => 'Antigravity']));
    }

    public function test_it_falls_back_to_default_locale() : void
    {
        $this->loader->addMessages('en', 'messages', [
            'welcome' => 'Welcome (EN)',
        ]);

        $this->translator->setLocale('fr');
        // 'fr' has no messages, so it should fall back to 'en'
        $this->assertSame('Welcome (EN)', $this->translator->get('messages.welcome'));
    }

    public function test_it_uses_requested_locale_over_current_locale() : void
    {
        $this->loader->addMessages('en', 'messages', ['welcome' => 'Welcome']);
        $this->loader->addMessages('fr', 'messages', ['welcome' => 'Bienvenue']);

        $this->assertSame('Bienvenue', $this->translator->get('messages.welcome', [], 'fr'));
        $this->assertSame('Welcome', $this->translator->get('messages.welcome'));
    }

    public function test_it_can_handle_namespaces() : void
    {
        $this->loader->addMessages('en', 'auth', [
            'failed' => 'Auth failed',
        ],                         'avax');

        $this->assertSame('Auth failed', $this->translator->get('avax::auth.failed'));
    }

    protected function setUp() : void
    {
        $this->loader     = new ArrayTranslationLoader();
        $this->translator = new Translator($this->loader, 'en', 'en');
    }
}
