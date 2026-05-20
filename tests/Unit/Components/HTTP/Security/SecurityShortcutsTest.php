<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\Security;

use PHPUnit\Framework\TestCase;

final class SecurityShortcutsTest extends TestCase
{
    public function test_load_order_all_functions_exist() : void
    {
        require __DIR__ . '/../../../../../components/HTTP/Security/System/PublicSurface/shortcuts.php';

        $this->assertTrue(function_exists('csrf_token'));
        $this->assertTrue(function_exists('csrf_field'));
        $this->assertTrue(function_exists('csrf_method'));
        $this->assertTrue(function_exists('secure_headers'));
    }

    public function test_csrf_token_owned_by_security_not_http_system() : void
    {
        require __DIR__ . '/../../../../../components/HTTP/Security/System/PublicSurface/shortcuts.php';

        $reflection = new \ReflectionFunction('csrf_token');
        $fileName = $reflection->getFileName();

        $this->assertStringContainsString(
            'HTTP/Security',
            $fileName,
            'csrf_token must be owned by HTTP/Security, not HTTP/System',
        );
    }

    public function test_secure_headers_no_deprecated_xss_protection() : void
    {
        $file = __DIR__ . '/../../../../../components/HTTP/Security/System/PublicSurface/shortcuts.php';

        $this->assertFileExists($file);

        $content = file_get_contents($file);
        $this->assertNotFalse($content);

        $this->assertStringNotContainsString(
            'X-XSS-Protection',
            $content,
            'secure_headers must not set the deprecated X-XSS-Protection header',
        );
    }

    public function test_secure_headers_includes_csp() : void
    {
        $file = __DIR__ . '/../../../../../components/HTTP/Security/System/PublicSurface/shortcuts.php';

        $content = file_get_contents($file);
        $this->assertNotFalse($content);

        $this->assertStringContainsString(
            'Content-Security-Policy',
            $content,
            'secure_headers should set a Content-Security-Policy header',
        );
    }

    public function test_secure_headers_includes_hsts() : void
    {
        $file = __DIR__ . '/../../../../../components/HTTP/Security/System/PublicSurface/shortcuts.php';

        $content = file_get_contents($file);
        $this->assertNotFalse($content);

        $this->assertStringContainsString(
            'Strict-Transport-Security',
            $content,
            'secure_headers should set a Strict-Transport-Security header',
        );
    }
}
