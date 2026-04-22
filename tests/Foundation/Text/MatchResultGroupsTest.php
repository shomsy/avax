<?php

declare(strict_types=1);

namespace Avax\Text\Tests;

use Avax\Text\Pattern;
use PHPUnit\Framework\TestCase;

final class MatchResultGroupsTest extends TestCase
{
    public function test_named_group_extraction() : void
    {
        $result = Pattern::of(raw: '(?<user>[^@]+)@(?<host>.+)')->match(subject: 'john@example.com');

        $this->assertTrue(condition: $result->matched);
        $this->assertEquals(expected: 'john', actual: $result->group(name: 'user'));
        $this->assertEquals(expected: 'example.com', actual: $result->group(name: 'host'));
    }

    public function test_group_returns_null_for_missing_named_group() : void
    {
        $result = Pattern::of(raw: '(\w+)')->match(subject: 'hello');

        $this->assertNull(actual: $result->group(name: 'missing'));
    }

    public function test_named_groups_returns_only_named_groups() : void
    {
        $result = Pattern::of(raw: '(?<first>\w+) (?<last>\w+)')->match(subject: 'John Doe');

        $named = $result->namedGroups();
        $this->assertEquals(expected: ['first' => 'John', 'last' => 'Doe'], actual: $named);
    }

    public function test_full_match_returns_index_zero() : void
    {
        $result = Pattern::of(raw: '\w+')->match(subject: 'hello');

        $this->assertEquals(expected: 'hello', actual: $result->fullMatch());
        $this->assertEquals(expected: $result->matches[0], actual: $result->fullMatch());
    }

    public function test_full_match_returns_null_when_no_match() : void
    {
        $result = Pattern::of(raw: '\d+')->match(subject: 'hello');

        $this->assertFalse(condition: $result->matched);
        $this->assertNull(actual: $result->fullMatch());
    }
}