<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\Configuration\PrepareRequest;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ProtocolVersion\NormalizeProtocolVersion;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestAttributes\RequestAttributes;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\ParsedBody;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseBodyByContentType;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseFormBody;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseJsonBody;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\RequestBody;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestCookies\RequestCookies;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Mapping\MapRequestedInputsToDto;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Sanitization\InputSanitizer;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestHeaders\RequestHeaders;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestInit;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerInit;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\NormalizeUploadedFiles;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\UploadedFiles;
use Avax\Components\HTTP\Request\ServerRequest\Network\ParseForwardedAddresses;
use Avax\Components\HTTP\Request\ServerRequest\Network\ResolveClientAddress;
use Avax\Components\HTTP\Request\ServerRequest\Network\TrustedProxyPolicy;
use Avax\Components\HTTP\Response\Capabilities\Streams\ResponseStreamFactory;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;

final class ReadIncomingHttpRequest
{
    private PrepareRequest $prepareRequest;

    public function __construct(private readonly ResponseStreamFactory $responseStreamFactory = new ResponseStreamFactory)
    {
        $trustedProxyPolicy = new TrustedProxyPolicy;

        $this->prepareRequest = new PrepareRequest(
            bodyParser        : new ParseBodyByContentType(
                                    jsonParser: new ParseJsonBody,
                                    formParser: new ParseFormBody,
            ),
            protocolNormalizer: new NormalizeProtocolVersion,
            filesNormalizer   : new NormalizeUploadedFiles,
            trustedProxyPolicy: $trustedProxyPolicy,
            clientResolver    : new ResolveClientAddress(
                proxyPolicy     : $trustedProxyPolicy,
                forwardedParser : new ParseForwardedAddresses,
            ),
        );
    }

    public function read(RuntimeRequest $request) : ServerRequest
    {
        $uri          = new Uri(uri: $this->normalizeUri(uri: $request->uri()));
        $body         = $request->body() ?? '';
        $queryParams  = $this->parseQueryParams(uri: $uri);
        $parsedBody   = $this->parseBody(headers: $request->headers(), body: $body);
        $requestState = RequestInit::fromResolvedParts(
            body           : new RequestBody(
                stream: $body === ''
                    ? $this->responseStreamFactory->createEmptyStream()
                    : $this->responseStreamFactory->createStreamFromString(content: $body),
            ),
            method         : $request->method(),
            uri            : $uri,
            requestHeaders : new RequestHeaders(headersInput: $request->headers()),
            serverParams   : $this->buildServerParams(uri: $uri, request: $request),
            explicitTarget : null,
            cookies        : new RequestCookies,
            queryParams    : $queryParams,
            uploadedFiles  : new UploadedFiles,
            parsedBody     : new ParsedBody(data: $parsedBody),
            attributes     : new RequestAttributes(attributes: $request->attributes()),
            session        : null,
            protocolVersion: '1.1',
        );

        return new ServerRequest(
            setup: new ServerInit(
                state     : $requestState,
                preparer  : $this->prepareRequest,
                sanitizer : new InputSanitizer,
                mapper    : new MapRequestedInputsToDto,
            ),
        );
    }

    private function normalizeUri(string $uri) : string
    {
        if (str_contains(haystack: $uri, needle: '://')) {
            return $uri;
        }

        $normalizedPath = str_starts_with(haystack: $uri, needle: '/')
            ? $uri
            : '/' . ltrim(string: $uri, characters: '/');

        return 'http://localhost' . $normalizedPath;
    }

    /**
     * @param array<string, list<string>> $headers
     */
    private function parseBody(array $headers, string $body) : array|object|null
    {
        $contentType = $this->headerLine(headers: $headers, name: 'Content-Type');

        return (new ParseBodyByContentType(
            jsonParser: new ParseJsonBody,
            formParser: new ParseFormBody,
        ))->execute(
            contentType: $contentType,
            content    : $body,
        );
    }

    private function parseQueryParams(UriInterface $uri) : array
    {
        $queryParams = [];
        parse_str(string: $uri->getQuery(), result: $queryParams);

        return $queryParams;
    }

    /**
     * @param array<string, list<string>> $headers
     *
     * @return array<string, mixed>
     */
    private function buildServerParams(UriInterface $uri, RuntimeRequest $request): array
    {
        $serverParams = [
            'REQUEST_METHOD' => $request->method(),
            'REQUEST_URI'    => $uri->getPath() . ($uri->getQuery() === '' ? '' : '?' . $uri->getQuery()),
            'HTTP_HOST'      => $uri->getHost() === '' ? 'localhost' : $uri->getHost(),
        ];

        if ($uri->getScheme() === 'https') {
            $serverParams['HTTPS'] = 'on';
        }

        foreach ($request->headers() as $name => $values) {
            $serverKey = strtoupper(string: str_replace(search: '-', replace: '_', subject: $name));

            if ($serverKey === 'CONTENT_TYPE' || $serverKey === 'CONTENT_LENGTH') {
                $serverParams[$serverKey] = implode(separator: ',', array: $values);

                continue;
            }

            $serverParams['HTTP_' . $serverKey] = implode(separator: ',', array: $values);
        }

        return $serverParams;
    }

    /**
     * @param array<string, list<string>> $headers
     */
    private function headerLine(array $headers, string $name) : string
    {
        foreach ($headers as $headerName => $values) {
            if (strcasecmp(string1: $headerName, string2: $name) !== 0) {
                continue;
            }

            return implode(separator: ',', array: $values);
        }

        return '';
    }
}
