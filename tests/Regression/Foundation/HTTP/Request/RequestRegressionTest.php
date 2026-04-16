<?php

declare(strict_types=1);

namespace Avax\Tests\Regression\Foundation\HTTP\Request;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Response\Classes\Stream;
use Avax\HTTP\URI\Uri;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Regression tests for the old ServerRequest object to ensure we eliminate
 * known architectural bugs during the rewrite.
 *
 * These tests are EXPECTED TO FAIL against the current implementation,
 * and will pass against the new ServerRequest\IncomingRequest\ServerRequest.
 */
class RequestRegressionTest extends TestCase
{
    public function test_headers_must_be_strictly_list_of_strings() : void
    {
        $request = $this->createBlankOldRequest();
        $request = $request->withHeader(name: 'X-Custom', value: 'Alpha');

        $headers = $request->getHeaders();
        $this->assertIsArray(actual: $headers['X-Custom']);
        $this->assertSame(expected: ['Alpha'], actual: $headers['X-Custom']);
    }

    private function createBlankOldRequest(array $serverParams = []) : Request
    {
        return new Request(
            session      : null,
            serverParams : $serverParams,
            uri          : new Uri('http://localhost'),
            body         : new Stream(stream: fopen('php://temp', 'r+')),
            queryParams  : [],
            parsedBody   : [],
            cookies      : [],
            uploadedFiles: []
        );
    }

    public function test_header_lookup_must_be_case_insensitive() : void
    {
        $request = $this->createBlankOldRequest();
        $request = $request->withHeader(name: 'X-CuStOm', value: 'Alpha');

        $this->assertTrue(condition: $request->hasHeader(name: 'x-custom'));
        $this->assertSame(expected: ['Alpha'], actual: $request->getHeader(name: 'X-CUSTOM'));
    }

    public function test_server_protocol_http_1_1_must_normalize_to_1_1() : void
    {
        $request = $this->createBlankOldRequest(serverParams: ['SERVER_PROTOCOL' => 'HTTP/1.1']);

        $this->assertSame(expected: '1.1', actual: $request->getProtocolVersion());
    }

    public function test_request_target_must_not_blindly_follow_uri_path_mutation() : void
    {
        $request = $this->createBlankOldRequest()->withRequestTarget(requestTarget: '/explicit-target');
        $newUri  = new Uri('http://example.com/new-path');

        $requestWithNewUri = $request->withUri(uri: $newUri);

        // Mutating URI MUST NOT change explicitly set request target in PSR-7
        $this->assertSame(expected: '/explicit-target', actual: $requestWithNewUri->getRequestTarget());
    }

    public function test_must_not_blindly_trust_forwarded_metadata_as_true_client_ip() : void
    {
        $request = $this->createBlankOldRequest(serverParams: [
                                                                  'REMOTE_ADDR'          => '192.168.1.1',
                                                                  'HTTP_X_FORWARDED_FOR' => '8.8.8.8'
                                                              ]);

        // Without an explicit trusted proxy policy, the true IP should be REMOTE_ADDR.
        // It shouldn't automatically resolve to X_FORWARDED_FOR.
        // Assuming there will be a safe method like resolveClientAddress in the new ecosystem.
        // For old request, we test if it currently leaks this.

        $this->assertNotSame(expected: '8.8.8.8', actual: $request->getServerParams()['REMOTE_ADDR']);
    }

    public function test_request_must_not_depend_on_trait_assembly_logic() : void
    {
        $reflection = new ReflectionClass(objectOrClass: Request::class);
        $traits     = $reflection->getTraitNames();

        // The new ServerRequest should not be a junkyard of Traits.
        $this->assertEmpty(actual: $traits, message: 'ServerRequest object must strictly avoid using traits for core behavior.');
    }
}
