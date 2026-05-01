<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Request\System\Flow\ReadRequest;

use Avax\Components\HTTP\Request\ParameterBag;
use Avax\Components\HTTP\Request\System\Capability\Input\InputAccessor;
use Avax\Components\HTTP\Request\System\Capability\Input\JsonBodyParser;
use Avax\Components\HTTP\Request\System\Capability\SessionBridge\RequestSessionBridge;
use Avax\Components\HTTP\Request\System\Flow\ReadRequest\ReadRequest;
use Avax\Components\HTTP\Request\System\Flow\ReadRequest\ReadRequestData;
use Avax\Components\HTTP\Request\System\Flow\ReadRequest\ReadRequestResult;
use Avax\Components\HTTP\Session\Shared\Contracts\SessionInterface;
use Avax\Tests\TestCase;

final class ReadRequestTest extends TestCase
{
    private ReadRequest $readRequest;

    public function test_execute_returns_result_with_all_inputs() : void
    {
        $data = new ReadRequestData(
            query  : ['foo' => 'query-foo'],
            body   : ['bar' => 'body-bar'],
            cookies: ['session' => 'cookie-session'],
            headers: ['Authorization' => 'Bearer token123'],
            files  : [],
            session: null,
        );

        $result = $this->readRequest->execute($data);

        $this->assertInstanceOf(expected: ReadRequestResult::class, actual: $result);
        $this->assertSame(expected: 'query-foo', actual: $result->query['foo']);
        $this->assertSame(expected: 'body-bar', actual: $result->input['bar']);
        $this->assertSame(expected: 'token123', actual: $result->bearerToken);
    }

    public function test_execute_includes_session_data() : void
    {
        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->willReturn(value: 'session-data');

        $data = new ReadRequestData(
            query  : [],
            body   : [],
            cookies: [],
            headers: [],
            files  : [],
            session: $session,
        );

        $result = $this->readRequest->execute($data);

        $this->assertNotNull(actual: $result->session);
    }

    public function test_execute_extracts_bearer_token() : void
    {
        $data = new ReadRequestData(
            query  : [],
            body   : [],
            cookies: [],
            headers: ['Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9'],
            files  : [],
            session: null,
        );

        $result = $this->readRequest->execute($data);

        $this->assertSame(expected: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9', actual: $result->bearerToken);
    }

    public function test_execute_returns_null_bearer_token_when_missing() : void
    {
        $data = new ReadRequestData(
            query  : [],
            body   : [],
            cookies: [],
            headers: [],
            files  : [],
            session: null,
        );

        $result = $this->readRequest->execute($data);

        $this->assertNull(actual: $result->bearerToken);
    }

    public function test_execute_includes_method_and_path() : void
    {
        $data = new ReadRequestData(
            query  : [],
            body   : [],
            cookies: [],
            headers: [],
            files  : [],
            session: null,
        );

        $result = $this->readRequest->execute($data, method: 'GET', path: '/test/path');

        $this->assertSame(expected: 'GET', actual: $result->method);
        $this->assertSame(expected: '/test/path', actual: $result->path);
    }

    protected function setUp() : void
    {
        $inputAccessor = new InputAccessor(
            query  : new ParameterBag(['foo' => 'query-foo']),
            body   : new ParameterBag(['bar' => 'body-bar']),
            cookies: new ParameterBag(['session' => 'cookie-session']),
            files  : new ParameterBag([]),
        );

        $sessionBridge = new RequestSessionBridge(
            $this->createMock(SessionInterface::class),
        );

        $jsonParser = new JsonBodyParser();

        $this->readRequest = new ReadRequest(
            inputAccessor: $inputAccessor,
            sessionBridge: $sessionBridge,
            jsonParser   : $jsonParser,
        );
    }
}
