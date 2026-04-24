<?php

declare(strict_types=1);

namespace Avax\HTTP\Response;

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
use Avax\HTTP\Response\Flows\EmitResponse\EmitResponse;
use Psr\Http\Message\ResponseInterface;

/**
 * Public facade for creating and emitting HTTP responses.
 *
 * This class provides a simple, predictable API for building common response types.
 * It delegates the actual building to specialized builders and emission to the emitter.
 */
final class Response
{
    /**
     * Create an empty response.
     */
    public static function empty() : ResponseInterface
    {
        return (new BuildEmptyResponse)();
    }

    /**
     * Create a text response.
     */
    public static function text(string $content, int $status = 200) : ResponseInterface
    {
        return (new BuildTextResponse)($content, $status);
    }

    /**
     * Create an HTML response.
     */
    public static function html(string $content, int $status = 200) : ResponseInterface
    {
        return (new BuildHtmlResponse)($content, $status);
    }

    /**
     * Create a JSON response.
     */
    public static function json(mixed $data, int $status = 200) : ResponseInterface
    {
        return (new BuildJsonResponse)($data, $status);
    }

    /**
     * Create an XML response.
     */
    public static function xml(string $xml, int $status = 200) : ResponseInterface
    {
        // Note: This assumes $xml is a well-formed XML string.
        return (new BuildXmlResponse)($xml, $status);
    }

    /**
     * Create a problem details response (RFC 7807).
     */
    public static function problem(string $title, int $status = 400, string $detail = '', string $type = 'about:blank') : ResponseInterface
    {
        return (new BuildProblemResponse)($title, $status, $detail, $type);
    }

    /**
     * Create a redirect response.
     */
    public static function redirect(string $url, int $status = 302) : ResponseInterface
    {
        return (new BuildRedirectResponse)($url, $status);
    }

    /**
     * Create a file download response.
     */
    public static function download(string $filePath, string $downloadName = null, int $status = 200) : ResponseInterface
    {
        return (new BuildFileDownloadResponse)($filePath, $downloadName, $status);
    }

    /**
     * Create a stream response.
     */
    public static function stream(resource $stream, int $status = 200) : ResponseInterface
    {
        return (new BuildStreamResponse)($stream, $status);
    }

    /**
     * Create a no-content response (status 204).
     */
    public static function noContent() : ResponseInterface
    {
        return (new BuildNoContentResponse)();
    }

    /**
     * Create a not-modified response (status 304).
     */
    public static function notModified() : ResponseInterface
    {
        return (new BuildNotModifiedResponse)();
    }

    /**
     * Emit a response.
     *
     * This method sends the response to the client and terminates script execution.
     */
    public static function emit(ResponseInterface $response) : void
    {
        (new EmitResponse)($response);
    }
}