<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\Configuration;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\ProtocolVersion\NormalizeProtocolVersion;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestAttributes\RequestAttributes;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\BodyAllowancePolicy;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\ParsedBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseBodyByContentType;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\RequestBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestCookies\RequestCookies;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestHeaders\RequestHeaders;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestInit;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestSession\RequestSession;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerEnvironment\ServerEnvironment;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\NormalizeUploadedFiles;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\UploadedFiles;
use Avax\HTTP\Request\ServerRequest\Network\ResolveClientAddress;
use Avax\HTTP\Request\ServerRequest\Network\TrustedIpv4ProxyPolicy;
use Avax\HTTP\Request\ServerRequest\Network\TrustedProxyPolicy;
use Avax\HTTP\Response\Capabilities\Streams\ResponseStreamFactory;
use Avax\HTTP\URI\UriBuilder;
use Psr\Http\Message\StreamInterface;
use ReflectionException;
use SensitiveParameter;

/**
 * PrepareRequest - Internal assembly pipeline owner.
 */
final readonly class PrepareRequest
{
    public function __construct(
        private ParseBodyByContentType                    $bodyParser,
        private NormalizeProtocolVersion                  $protocolNormalizer,
        private NormalizeUploadedFiles                    $filesNormalizer,
        private TrustedProxyPolicy|TrustedIpv4ProxyPolicy $trustedProxyPolicy,
        private ResolveClientAddress                      $clientResolver,
        private BodyAllowancePolicy                       $bodyAllowance = new BodyAllowancePolicy(),
    ) {}

    /**
     * @throws ReflectionException
     */
    public function fromGlobals(
        array|null  $server = null,
        array|null  $query = null,
        array|null  $cookie = null,
        array|null  $files = null,
        string|null $rawBody = null,
    ) : RequestInit
    {
        $server = $server ?? $_SERVER ?? [];
        $query  = $query ?? $_GET ?? [];
        $cookie = $cookie ?? $_COOKIE ?? [];
        $files  = $files ?? $_FILES ?? [];

        // Use typed ServerEnvironment
        $env = ServerEnvironment::fromServerParams(serverParams: $server);

        $method          = $this->stageReadMethod(env: $env);
        $protocolVersion = $this->stageReadProtocol(env: $env);
        $uri             = $this->stageReadUri(env: $env);
        $requestHeaders  = $this->stageExtractHeaders(server: $server);

        // Capture raw body once to avoid multiple IO reads
        $rawBody = $this->captureRawBody(
            method : $method,
            headers: $requestHeaders->all(),
            rawBody: $rawBody,
        );

        $bodyStream = $this->stageResolveBodyStream(rawBody: $rawBody);
        $parsedBody = $this->stageParseBody(rawBody: $rawBody, requestHeaders: $requestHeaders);
        $session    = $this->stageResolveSession();

        return RequestInit::fromResolvedParts(
            body           : new RequestBody(stream: $bodyStream),
            method         : $method,
            uri            : $uri,
            requestHeaders : $requestHeaders,
            serverParams   : $server,
            explicitTarget : null,
            cookies        : new RequestCookies(cookies: $cookie),
            queryParams    : $query,
            uploadedFiles  : new UploadedFiles(files: $this->filesNormalizer->execute(files: $files)),
            parsedBody     : $parsedBody,
            attributes     : new RequestAttributes,
            session        : $session,
            protocolVersion: $protocolVersion,
        );
    }

    private function stageReadMethod(ServerEnvironment $env) : string
    {
        return strtoupper(string: $env->requestMethod ?? 'GET');
    }

    private function stageReadProtocol(ServerEnvironment $env) : string
    {
        return $this->protocolNormalizer->execute(
            protocol: $env->serverProtocol ?? '1.1'
        );
    }

    private function stageReadUri(ServerEnvironment $env) : UriBuilder
    {
        $protocol = $env->isHttps() ? 'https' : 'http';
        $host     = $env->httpHost ?? 'localhost';
        $uri      = $env->requestUri ?? '/';

        return UriBuilder::createFromString(uri: "{$protocol}://{$host}{$uri}");
    }

    private function stageExtractHeaders(array $server) : RequestHeaders
    {
        $headers = [];
        foreach ($server as $key => $value) {
            if (str_starts_with(haystack: $key, needle: 'HTTP_')) {
                $name           = str_replace(search: '_', replace: '-', subject: substr(string: $key, offset: 5));
                $headers[$name] = $value;
            } elseif (in_array(needle: $key, haystack: ['CONTENT_TYPE', 'CONTENT_LENGTH'], strict: true)) {
                $name           = str_replace(search: '_', replace: '-', subject: $key);
                $headers[$name] = $value;
            }
        }

        return new RequestHeaders(headersInput: $headers);
    }

    private function captureRawBody(string $method, #[SensitiveParameter] array $headers, string|null $rawBody = null) : string
    {
        if ($rawBody !== null) {
            return $rawBody;
        }

        if (! $this->bodyAllowance->allowsRead(method: $method, headers: $headers)) {
            return '';
        }

        $content = file_get_contents(filename: 'php://input');

        return $content !== false ? $content : '';
    }

    private function stageResolveBodyStream(string $rawBody) : StreamInterface
    {
        if ($rawBody === '') {
            return $this->createEmptyBodyStream();
        }

        return $this->createBodyStreamFromRaw(rawBody: $rawBody);
    }

    private function createEmptyBodyStream() : StreamInterface
    {
        return new ResponseStreamFactory()->createEmptyStream();
    }

    private function createBodyStreamFromRaw(string $rawBody) : StreamInterface
    {
        return new ResponseStreamFactory()->createStreamFromString(content: $rawBody);
    }

    private function stageParseBody(string $rawBody, #[SensitiveParameter] RequestHeaders $requestHeaders) : ParsedBody
    {
        if ($rawBody === '') {
            return new ParsedBody();
        }

        $contentType = $requestHeaders->getLine(name: 'Content-Type');
        $parsedData  = $this->bodyParser->execute(
            contentType: $contentType,
            content    : $rawBody,
        );

        // Standardized semantics: null = no parser/no content, [] = valid empty content
        return new ParsedBody(data: $parsedData);
    }

    private function stageResolveSession() : RequestSession|null
    {
        $status = session_status();

        if ($status !== PHP_SESSION_ACTIVE || $_SESSION === []) {
            return null;
        }

        return new RequestSession(data: $_SESSION);
    }

    public function fromSlices(
        array|null        $queryParams = null,
        array|object|null $parsedBody = null,
        string            $method = 'GET',
    ) : RequestInit
    {
        $queryParams    ??= [];
        $parsedBodyData = match (true) {
            is_array(value: $parsedBody)  => $parsedBody,
            is_object(value: $parsedBody) => (array) $parsedBody,
            $parsedBody === null          => null,
            default                       => [],
        };

        return $this->defaults()
            ->withMethod(method: $method)
            ->withQueryParams(queryParams: $queryParams)
            ->withParsedBody(parsedBody: new ParsedBody(data: $parsedBodyData));
    }

    public function defaults() : RequestInit
    {
        $uri            = UriBuilder::createFromString(uri: 'http://localhost');
        $requestHeaders = new RequestHeaders;

        return RequestInit::fromResolvedParts(
            body           : new RequestBody(stream: $this->createEmptyBodyStream()),
            method         : 'GET',
            uri            : $uri,
            requestHeaders : $requestHeaders,
            serverParams   : [],
            explicitTarget : '',
            cookies        : new RequestCookies,
            queryParams    : [],
            uploadedFiles  : new UploadedFiles,
            parsedBody     : new ParsedBody,
            attributes     : new RequestAttributes,
            session        : null,
            protocolVersion: '1.1',
        );
    }

    public function resolveClientAddress(array $serverParams) : string|null
    {
        $remoteAddr = $serverParams['REMOTE_ADDR'] ?? null;

        if ($remoteAddr === null) {
            return null;
        }

        $headers = $this->stageExtractHeaders(server: $serverParams);

        return $this->clientResolver->execute(
            remoteAddr: $remoteAddr,
            headers   : $headers,
        );
    }

    public function isFromTrustedProxy(array $serverParams) : bool
    {
        $remoteAddr = $serverParams['REMOTE_ADDR'] ?? null;

        return $remoteAddr !== null
            && $this->trustedProxyPolicy->isTrusted(ip: $remoteAddr);
    }
}
