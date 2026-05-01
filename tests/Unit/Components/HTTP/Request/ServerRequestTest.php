<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\Request;

use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Tests\TestCase;

final class ServerRequestTest extends TestCase
{
    public function test_with_header_preserves_existing_headers() : void
    {
        $request = new ServerRequest(headers: ['Accept' => 'application/json']);

        $next = $request->withHeader(name: 'X-Trace-Id', value: 'trace-1');

        self::assertSame(expected: ['application/json'], actual: $next->getHeader(name: 'Accept'));
        self::assertSame(expected: ['trace-1'], actual: $next->getHeader(name: 'X-Trace-Id'));
    }

    public function test_from_globals_normalizes_uploaded_file_with_readable_stream() : void
    {
        $server  = $_SERVER;
        $get     = $_GET;
        $post    = $_POST;
        $cookie  = $_COOKIE;
        $files   = $_FILES;
        $tmpFile = tempnam(directory: sys_get_temp_dir(), prefix: 'avax-upload-');

        self::assertIsString(actual: $tmpFile);

        file_put_contents(filename: $tmpFile, data: 'abc');

        try {
            $_SERVER = ['REQUEST_METHOD' => 'POST'];
            $_GET    = [];
            $_POST   = [];
            $_COOKIE = [];
            $_FILES  = [
                'avatar' => [
                    'tmp_name' => $tmpFile,
                    'size'     => 3,
                    'error'    => UPLOAD_ERR_OK,
                    'name'     => 'avatar.txt',
                    'type'     => 'text/plain',
                ],
            ];

            $request      = ServerRequest::fromGlobals();
            $uploadedFile = $request->getUploadedFiles()['avatar'];

            self::assertSame(expected: 'avatar.txt', actual: $uploadedFile->getClientFilename());
            self::assertSame(expected: 'abc', actual: (string) $uploadedFile->getStream());
        } finally {
            $_SERVER = $server;
            $_GET    = $get;
            $_POST   = $post;
            $_COOKIE = $cookie;
            $_FILES  = $files;

            if (is_file(filename: $tmpFile)) {
                unlink(filename: $tmpFile);
            }
        }
    }
}
