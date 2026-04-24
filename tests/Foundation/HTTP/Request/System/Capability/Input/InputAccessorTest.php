<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Request\System\Capability\Input;

use Avax\HTTP\Request\ParameterBag;
use Avax\HTTP\Request\System\Capability\Input\InputAccessor;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

final class InputAccessorTest extends TestCase
{
    private InputAccessor $accessor;

    public function test_input_returns_value_from_query_params() : void
    {
        $result = $this->accessor->input('foo');

        $this->assertSame(expected: 'query-foo', actual: $result);
    }

    public function test_input_falls_back_from_query_to_body() : void
    {
        $fooResult = $this->accessor->input('foo');
        $barResult = $this->accessor->input('bar');

        $this->assertSame(expected: 'query-foo', actual: $fooResult);
        $this->assertSame(expected: 'body-bar', actual: $barResult);
    }

    public function test_input_returns_default_when_not_found() : void
    {
        $result = $this->accessor->input('missing', 'default');

        $this->assertSame(expected: 'default', actual: $result);
    }

    public function test_query_returns_value_from_query_params() : void
    {
        $result = $this->accessor->query('foo');

        $this->assertSame(expected: 'query-foo', actual: $result);
    }

    public function test_query_returns_default_when_missing() : void
    {
        $result = $this->accessor->query('missing', 'default-value');

        $this->assertSame(expected: 'default-value', actual: $result);
    }

    public function test_has_returns_true_if_key_in_query() : void
    {
        $this->assertTrue(condition: $this->accessor->has('foo'));
    }

    public function test_has_returns_true_if_key_in_body() : void
    {
        $this->assertTrue(condition: $this->accessor->has('bar'));
    }

    public function test_has_returns_false_when_key_not_found() : void
    {
        $this->assertFalse(condition: $this->accessor->has('missing'));
    }

    public function test_cookie_returns_value_from_cookies() : void
    {
        $result = $this->accessor->cookie('session');

        $this->assertSame(expected: 'cookie-session', actual: $result);
    }

    public function test_cookie_returns_default_when_missing() : void
    {
        $result = $this->accessor->cookie('missing', 'default');

        $this->assertSame(expected: 'default', actual: $result);
    }

    public function test_file_returns_value_from_files() : void
    {
        $result = $this->accessor->file('document');

        $this->assertSame(expected: ['name' => 'test.pdf'], actual: $result);
    }

    public function test_file_returns_default_when_missing() : void
    {
        $result = $this->accessor->file('missing', 'default');

        $this->assertSame(expected: 'default', actual: $result);
    }

    public function test_all_inputs_merges_all_sources() : void
    {
        $result = $this->accessor->allInputs();

        $this->assertArrayHasKey(key: 'foo', array: $result);
        $this->assertArrayHasKey(key: 'bar', array: $result);
        $this->assertArrayHasKey(key: 'shared', array: $result);
        $this->assertArrayHasKey(key: 'session', array: $result);
        $this->assertArrayHasKey(key: 'document', array: $result);
    }

    public function test_merge_injects_into_query_and_body() : void
    {
        $this->accessor->merge(['new-key' => 'new-value']);

        $this->assertSame(expected: 'new-value', actual: $this->accessor->input('new-key'));
    }

    public function test_merge_respects_overwrite_flag() : void
    {
        $this->accessor->merge(['foo' => 'overwritten'], overwrite: false);

        $this->assertSame(expected: 'query-foo', actual: $this->accessor->input('foo'));
    }

    protected function setUp() : void
    {
        $queryBag   = new ParameterBag(['foo' => 'query-foo', 'shared' => 'query-shared']);
        $bodyBag    = new ParameterBag(['bar' => 'body-bar', 'shared' => 'body-shared']);
        $cookiesBag = new ParameterBag(['session' => 'cookie-session']);
        $filesBag   = new ParameterBag(['document' => ['name' => 'test.pdf']]);

        $this->accessor = new InputAccessor(
            query  : $queryBag,
            body   : $bodyBag,
            cookies: $cookiesBag,
            files  : $filesBag
        );
    }
}
