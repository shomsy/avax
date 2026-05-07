<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Security\System;

use Avax\Components\Security\System\Capabilities\Escape\OutputEscaper;
use Avax\Components\Security\System\Capabilities\MassAssignment\MassAssignmentGuard;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SecuritySystemCapabilitiesTest extends TestCase
{
    // --- OutputEscaper ---

    public function test_it_escapes_html_special_characters() : void
    {
        $result = OutputEscaper::html('<script>alert("xss")</script>');
        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    public function test_it_escapes_attribute_context() : void
    {
        $result = OutputEscaper::attribute('" onmouseover="alert(1)');
        $this->assertStringNotContainsString('"', $result);
    }

    public function test_it_escapes_json_with_html_safe_flags() : void
    {
        $result = OutputEscaper::json(['tag' => '<b>bold</b>']);
        $this->assertStringNotContainsString('<', $result);
        $this->assertStringContainsString('\u003C', $result);
    }

    public function test_it_preserves_safe_text_in_html_escape() : void
    {
        $this->assertSame('Hello World', OutputEscaper::html('Hello World'));
    }

    // --- MassAssignmentGuard ---

    public function test_it_returns_only_fillable_fields() : void
    {
        $guard  = new MassAssignmentGuard();
        $result = $guard->onlyFillable(
            ['name' => 'John', 'email' => 'john@example.com'],
            ['name', 'email'],
        );
        $this->assertSame(['name' => 'John', 'email' => 'john@example.com'], $result);
    }

    public function test_it_throws_when_unknown_keys_present() : void
    {
        $guard = new MassAssignmentGuard();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('is_admin');
        $guard->onlyFillable(
            ['name' => 'John', 'is_admin' => true],
            ['name'],
        );
    }

    public function test_it_allows_subset_of_fillable_fields() : void
    {
        $guard  = new MassAssignmentGuard();
        $result = $guard->onlyFillable(
            ['name' => 'John'],
            ['name', 'email', 'bio'],
        );
        $this->assertSame(['name' => 'John'], $result);
    }
}
