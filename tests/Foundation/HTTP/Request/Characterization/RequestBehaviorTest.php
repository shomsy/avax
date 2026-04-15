<?php

declare(strict_types=1);

namespace Avax\HTTP\Tests\Foundation\Request\Characterization;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Session\NullSession;
use Avax\HTTP\URI\UriBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Characterization tests for ServerRequest class behavior.
 *
 * These tests capture the current behavior of the ServerRequest class
 * to ensure refactoring doesn't break existing functionality.
 */
final class RequestBehaviorTest extends TestCase
{
    public function test_request_can_be_instantiated() : void
    {
        $request = $this->createRequest();

        $this->assertInstanceOf(expected: Request::class, actual: $request);
    }

    private function createRequest(
        array|null $serverParams = null,
        array|null $queryParams = null,
        array      $parsedBody = [],
    ) : Request
    {
        $serverParams ??= [];
        $queryParams  ??= [];
        $uri          = UriBuilder::createFromString(uri: 'http://localhost/test');

        return new Request(
            session     : new NullSession(),
            serverParams: array_merge(['REQUEST_METHOD' => 'GET'], $serverParams),
            uri         : $uri,
            queryParams : $queryParams,
            parsedBody  : $parsedBody,
        );
    }

    public function test_input_returns_value_from_query_params() : void
    {
        $request = $this->createRequest(
            queryParams: ['foo' => 'query-foo', 'shared' => 'query-shared'],
            parsedBody : ['bar' => 'request-bar', 'shared' => 'request-shared']
        );

        $this->assertSame(expected: 'query-foo', actual: $request->input(key: 'foo'));
    }

    public function test_input_falls_back_from_query_to_body() : void
    {
        $request = $this->createRequest(
            serverParams: ['REQUEST_METHOD' => 'POST'],
            queryParams : ['foo' => 'query-foo'],
            parsedBody  : ['bar' => 'request-bar']
        );

        $this->assertSame(expected: 'query-foo', actual: $request->input(key: 'foo'));
        $this->assertSame(expected: 'request-bar', actual: $request->input(key: 'bar'));
    }

    public function test_input_returns_default_when_not_found() : void
    {
        $request = $this->createRequest();

        $this->assertSame(expected: 'default', actual: $request->input(key: 'missing', default: 'default'));
    }

    public function test_query_returns_value_from_query_params() : void
    {
        $request = $this->createRequest(
            queryParams: ['foo' => 'query-value']
        );

        $this->assertSame(expected: 'query-value', actual: $request->query('foo'));
    }

    public function test_query_returns_default_when_missing() : void
    {
        $request = $this->createRequest();

        $this->assertSame(expected: 'default', actual: $request->query('missing', 'default'));
    }

    public function test_has_returns_true_if_key_in_query() : void
    {
        $request = $this->createRequest(
            queryParams: ['foo' => 'bar']
        );

        $this->assertTrue(condition: $request->has('foo'));
    }

    public function test_has_returns_false_when_key_not_found() : void
    {
        $request = $this->createRequest();

        $this->assertFalse(condition: $request->has('missing'));
    }

    public function test_get_is_alias_for_input() : void
    {
        $request = $this->createRequest(
            queryParams: ['foo' => 'bar']
        );

        $this->assertSame(expected: $request->input(key: 'foo'), actual: $request->get('foo'));
    }

    public function test_get_query_params_returns_query_array() : void
    {
        $request = $this->createRequest(
            queryParams: ['foo' => 'bar', 'baz' => 'qux']
        );

        $this->assertSame(expected: ['foo' => 'bar', 'baz' => 'qux'], actual: $request->getQueryParams());
    }

    public function test_get_parsed_body_returns_body_array() : void
    {
        $request = $this->createRequest(
            serverParams: ['REQUEST_METHOD' => 'POST'],
            parsedBody  : ['name' => 'John']
        );

        $this->assertSame(expected: ['name' => 'John'], actual: $request->getParsedBody());
    }

    /**
     * @group integration
     *
     * This test requires container to be initialized.
     */
    public function test_session_returns_session_instance_when_no_key() : void
    {
        $session = new NullSession();
        $uri     = UriBuilder::createFromString(uri: 'http://localhost/test');
        $request = new Request(session: $session, uri: $uri);

        $this->assertSame(expected: $session, actual: $request->session());
    }

    /**
     * @group integration
     *
     * This test requires container to be initialized.
     */
    public function test_session_returns_value_with_key() : void
    {
        $request = $this->createRequest();

        $result = $request->session('missing', 'default');
        $this->assertSame(expected: 'default', actual: $result);
    }

    public function test_has_session_checks_key_existence() : void
    {
        $request = $this->createRequest();

        $this->assertFalse(condition: $request->hasSession('any-key'));
    }

    public function test_put_session_stores_value() : void
    {
        $request = $this->createRequest();
        $request->putSession('key', 'value');

        $this->assertTrue(condition: $request->hasSession('key'));
    }

    public function test_forget_session_removes_value() : void
    {
        $request = $this->createRequest();
        $request->putSession('key', 'value');
        $request->forgetSession('key');

        $this->assertFalse(condition: $request->hasSession('key'));
    }

    public function test_path_returns_uri_path() : void
    {
        $uri     = UriBuilder::createFromString(uri: 'http://example.com/test/path?foo=bar');
        $request = new Request(
            session     : new NullSession(),
            serverParams: [
                              'REQUEST_METHOD' => 'GET',
                          ],
            uri         : $uri
        );

        $this->assertSame(expected: '/test/path', actual: $request->path());
    }

    public function test_get_method_returns_http_method() : void
    {
        $request = $this->createRequest(
            serverParams: ['REQUEST_METHOD' => 'POST']
        );

        $this->assertSame(expected: 'POST', actual: $request->getMethod());
    }

    public function test_route_returns_attribute() : void
    {
        $request = $this->createRequest();
        $request = $request->withAttribute(name: 'user_id', value: 123);

        $this->assertSame(expected: 123, actual: $request->route('user_id'));
        $this->assertSame(expected: 'default', actual: $request->route('missing', 'default'));
    }

    public function test_user_returns_null_when_not_in_session() : void
    {
        $request = $this->createRequest();

        $this->assertNull(actual: $request->user());
    }

    /**
     * @group integration
     *
     * This test requires container to be initialized.
     */
    public function test_get_returns_session_value() : void
    {
        $request = $this->createRequest();
        $request->putSession('key', 'value');

        $result = $request->session('key');
        $this->assertSame(expected: 'value', actual: $result);
    }
}
