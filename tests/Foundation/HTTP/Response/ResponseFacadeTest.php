<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Response;

use Avax\HTTP\Response\Capabilities\Caching\CacheControl;
use Avax\HTTP\Response\Capabilities\Caching\Etag;
use Avax\HTTP\Response\Capabilities\Caching\LastModified;
use Avax\HTTP\Response\Response;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ResponseFacadeTest extends TestCase
{
    public function test_json_problem_and_xml_responses_use_specialized_builders() : void
    {
        $json    = Response::json(data: ['name' => 'Alice'], status: 201);
        $problem = Response::problem(title: 'Validation failed', status: 422, detail: 'Email is required');
        $xml     = Response::xml(xml: ['name' => 'Alice', 'meta' => ['city' => 'Belgrade']]);

        self::assertSame(201, $json->getStatusCode());
        self::assertSame('application/json', $json->getHeaderLine(name: 'Content-Type'));
        self::assertSame(['name' => 'Alice'], json_decode(json: (string) $json->getBody(), associative: true));

        self::assertSame('application/problem+json', $problem->getHeaderLine(name: 'Content-Type'));
        self::assertSame(422, $problem->getStatusCode());
        self::assertSame(
            [
                'type'   => 'about:blank',
                'title'  => 'Validation failed',
                'status' => 422,
                'detail' => 'Email is required',
            ],
            json_decode(json: (string) $problem->getBody(), associative: true),
        );

        self::assertSame('application/xml; charset=UTF-8', $xml->getHeaderLine(name: 'Content-Type'));
        self::assertStringContainsString('<name>Alice</name>', (string) $xml->getBody());
        self::assertStringContainsString('<city>Belgrade</city>', (string) $xml->getBody());
    }

    public function test_redirect_download_and_stream_responses_cover_core_http_shapes() : void
    {
        $redirect = Response::redirect(url: '/dashboard', status: 303);

        $streamHandle = fopen(filename: 'php://temp', mode: 'r+');
        fwrite(stream: $streamHandle, data: 'streamed');
        rewind(stream: $streamHandle);
        $stream = Response::stream(stream: $streamHandle, status: 206, headers: ['Content-Type' => 'text/plain']);

        $path = tempnam(directory: sys_get_temp_dir(), prefix: 'response-download-');
        self::assertNotFalse($path);
        file_put_contents(filename: $path, data: 'download payload');

        try {
            $download = Response::download(filePath: $path, downloadName: 'report.txt');

            self::assertSame(303, $redirect->getStatusCode());
            self::assertSame('/dashboard', $redirect->getHeaderLine(name: 'Location'));

            self::assertSame(206, $stream->getStatusCode());
            self::assertSame('streamed', (string) $stream->getBody());

            self::assertSame(200, $download->getStatusCode());
            self::assertSame('attachment; filename="report.txt"', $download->getHeaderLine(name: 'Content-Disposition'));
            self::assertSame('download payload', (string) $download->getBody());
        } finally {
            @unlink(filename: $path);
        }
    }

    public function test_no_content_and_not_modified_responses_keep_http_invariants() : void
    {
        $noContent   = Response::noContent();
        $notModified = Response::notModified(
            etag        : new Etag(value: 'abc123'),
            lastModified: new LastModified(value: new DateTimeImmutable(datetime: '2026-01-01T00:00:00+00:00')),
            cacheControl: new CacheControl(directives: ['public' => true, 'max-age' => 60]),
        );

        self::assertSame(204, $noContent->getStatusCode());
        self::assertSame('', (string) $noContent->getBody());

        self::assertSame(304, $notModified->getStatusCode());
        self::assertSame('"abc123"', $notModified->getHeaderLine(name: 'ETag'));
        self::assertSame('public, max-age=60', $notModified->getHeaderLine(name: 'System-Control'));
        self::assertSame('', $notModified->getHeaderLine(name: 'Content-Type'));
        self::assertSame('', (string) $notModified->getBody());
    }
}
