<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Text;

use Avax\Tests\TestCase;
use components\Text\Pattern;

final class PatternFlagsPropagationTest extends TestCase
{
    public function test_case_insensitive_flag() : void
    {
        $pattern = Pattern::of(raw: 'hello', flags: 'i');
        $this->assertTrue(condition: $pattern->test(subject: 'HELLO'));
        $this->assertTrue(condition: $pattern->test(subject: 'Hello'));
        $this->assertTrue(condition: $pattern->test(subject: 'hello'));
    }

    public function test_multiline_flag() : void
    {
        $pattern = Pattern::of(raw: '^world', flags: 'm');
        $this->assertTrue(condition: $pattern->test(subject: "hello\nworld"));
        $this->assertFalse(condition: $pattern->test("hello\nworld", ''));
    }

    public function test_unicode_flag() : void
    {
        $pattern = Pattern::of(raw: 'šđčćž', flags: 'u');
        $this->assertTrue(condition: $pattern->test(subject: 'šđčćž'));
    }

    public function test_anchored_flag() : void
    {
        $pattern = Pattern::of(raw: '^hello', flags: 'A'); // PCRE_ANCHORED
        $this->assertTrue(condition: $pattern->test(subject: 'hello world'));
        $this->assertFalse(condition: $pattern->test(subject: 'world hello'));
    }

    public function test_combined_flags() : void
    {
        $pattern = Pattern::of(raw: 'HELLO', flags: 'iu');
        $this->assertTrue(condition: $pattern->test(subject: 'hello'));
        $this->assertTrue(condition: $pattern->test(subject: 'HELLO'));
        $this->assertTrue(condition: $pattern->test(subject: 'héllo')); // Unicode test
    }
}
