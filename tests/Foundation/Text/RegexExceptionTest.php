<?php

declare(strict_types=1);

namespace Avax\Text\Tests;

use Avax\Text\Pattern;
use Avax\Text\RegexException;
use PHPUnit\Framework\TestCase;

final class RegexExceptionTest extends TestCase
{
    public function test_invalid_pattern_throws() : void
    {
        $this->expectException(exception: RegexException::class);
        $this->expectExceptionMessage(message: 'for pattern:');

        Pattern::of(raw: '[invalid')->test(subject: 'subject');
    }

    public function test_replace_callback_returns_null_throws() : void
    {
        $this->expectException(exception: RegexException::class);

        Pattern::of(raw: '(\w+)')->replaceCallback(subject: 'hello world', fn: fn($matches) => null);
    }

    public function test_preg_last_error_mapping() : void
    {
        try {
            Pattern::of(raw: '[invalid')->test(subject: 'subject');
            $this->fail(message: 'Should have thrown RegexException');
        } catch (RegexException $e) {
            $this->assertStringContains('for pattern:', $e->getMessage());
            $this->assertIsInt(actual: $e->getCode());
            $this->assertGreaterThan(expected: 0, actual: $e->getCode());
        }
    }
}