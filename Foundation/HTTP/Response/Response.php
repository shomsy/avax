<?php

declare(strict_types=1);

namespace Avax\HTTP\Response;

use Avax\HTTP\Response\Capabilities\Caching\CacheControl;
use Avax\HTTP\Response\Capabilities\Caching\Etag;
use Avax\HTTP\Response\Capabilities\Caching\LastModified;
use Avax\HTTP\Response\Flows\BuildResponse\BuildEmptyResponse;
use Avax\HTTP\Response\Flows\BuildResponse\BuildFileDownloadResponse;
use Avax\HTTP\Response\Flows\BuildResponse\BuildHtmlResponse;
use Avax\HTTP\Response\Flows\BuildResponse\BuildJsonResponse;
use Avax\HTTP\Response\Flows\BuildResponse\BuildNoContentResponse;
use Avax\HTTP\Response\Flows\BuildResponse\BuildNotModifiedResponse;
use Avax\HTTP\Response\Flows\BuildResponse\BuildProblemResponse;
use Avax\HTTP\Response\Flows\BuildResponse\BuildRedirectResponse;
use Avax\HTTP\Response\Flows\BuildResponse\BuildStreamResponse;
use Avax\HTTP\Response\Flows\BuildResponse\BuildTextResponse;
use Avax\HTTP\Response\Flows\BuildResponse\BuildXmlResponse;
use Psr\Http\Message\ResponseInterface;
use SensitiveParameter;

/**
 * Small public facade for building and emitting HTTP responses.
 */
final class Response
{
    public static function empty(int|null $status = null, #[SensitiveParameter] array|null $headers = null, string $reasonPhrase = '') : ResponseInterface
    {
        $status  ??= 200;
        $headers ??= [];

        return new BuildEmptyResponse()(
            status      : $status,
            headers     : $headers,
            reasonPhrase: $reasonPhrase,
        );
    }

    public static function text(string $content, int|null $status = null, #[SensitiveParameter] array $headers = []) : ResponseInterface
    {
        $status ??= 200;

        return new BuildTextResponse()(content: $content, status: $status, headers: $headers);
    }

    public static function html(string $content, int|null $status = null, #[SensitiveParameter] array $headers = []) : ResponseInterface
    {
        $status ??= 200;

        return new BuildHtmlResponse()(content: $content, status: $status, headers: $headers);
    }

    public static function json(mixed $data, int|null $status = null, #[SensitiveParameter] array $headers = []) : ResponseInterface
    {
        $status ??= 200;

        return new BuildJsonResponse()(data: $data, status: $status, headers: $headers);
    }

    public static function xml(string|array $xml, int|null $status = null, #[SensitiveParameter] array $headers = []) : ResponseInterface
    {
        $status ??= 200;

        return new BuildXmlResponse()(xml: $xml, status: $status, headers: $headers);
    }

    public static function problem(
        string      $title,
        int|null    $status = null,
        string|null $detail = null,
        string|null $type = null,
        array       $extensions = []
    ) : ResponseInterface
    {
        $status ??= 400;
        $detail ??= '';
        $type   ??= 'about:blank';

        return new BuildProblemResponse()(
            title     : $title,
            status    : $status,
            detail    : $detail,
            type      : $type,
            extensions: $extensions,
        );
    }

    public static function redirect(string $url, int|null $status = null, #[SensitiveParameter] array $headers = []) : ResponseInterface
    {
        $status ??= 302;

        return new BuildRedirectResponse()(target: $url, status: $status, headers: $headers);
    }

    public static function download(string $filePath, string|null $downloadName = null, int|null $status = null, #[SensitiveParameter] array $headers = []) : ResponseInterface
    {
        $status ??= 200;

        return new BuildFileDownloadResponse()(filePath: $filePath, downloadName: $downloadName, status: $status, headers: $headers);
    }

    public static function stream(mixed $stream, int|null $status = null, #[SensitiveParameter] array $headers = []) : ResponseInterface
    {
        $status ??= 200;

        return new BuildStreamResponse()(stream: $stream, status: $status, headers: $headers);
    }

    public static function noContent(#[SensitiveParameter] array $headers = []) : ResponseInterface
    {
        return new BuildNoContentResponse()(headers: $headers);
    }

    public static function notModified(
        Etag|null                   $etag = null,
        LastModified|null           $lastModified = null,
        CacheControl|null           $cacheControl = null,
        #[SensitiveParameter] array $headers = []
    ) : ResponseInterface
    {
        return new BuildNotModifiedResponse()(etag: $etag, lastModified: $lastModified, cacheControl: $cacheControl, headers: $headers);
    }

    public static function emit(ResponseInterface $response) : void
    {
        new ResponseEmitter()->emit(response: $response);
    }
}
