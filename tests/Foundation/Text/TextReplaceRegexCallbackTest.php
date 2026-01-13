<?php

declare(strict_types=1);

namespace Avax\Text\Tests;

use Avax\Text\RegexException;
use Avax\Text\Text;
use PHPUnit\Framework\TestCase;

final class TextReplaceRegexCallbackTest extends TestCase
{
    public function test_replace_regex_callback_basic() : void
    {
        $result = Text::of(value: 'hello world')
            ->replaceRegexCallback(pattern: '(\w+)', fn: fn($matches) => strtoupper($matches[0]))
            ->toString();

        $this->assertEquals(expected: 'HELLO WORLD', actual: $result);
    }

    public function test_replace_regex_callback_with_groups() : void
    {
        $result = Text::of(value: 'user: john, age: 25')
            ->replaceRegexCallback(pattern: '(\w+): (\w+)', fn: fn($matches) => $matches[1] . '=' . $matches[2])
            ->toString();

        $this->assertEquals(expected: 'user=john, age=25', actual: $result);
    }

    public function test_replace_regex_callback_returns_null_throws() : void
    {
        $this->expectException(exception: RegexException::class);

        Text::of(value: 'test')
            ->replaceRegexCallback(pattern: '(\w+)', fn: fn($matches) => null)
            ->toString();
    }

    public function test_replace_regex_callback_with_flags() : void
    {
        $result = Text::of(value: 'HELLO world')
            ->replaceRegexCallback(pattern: '(\w+)', fn: fn($matches) => strtolower($matches[0]), flags: 'i')
            ->toString();

        $this->assertEquals(expected: 'hello world', actual: $result);
    }
}