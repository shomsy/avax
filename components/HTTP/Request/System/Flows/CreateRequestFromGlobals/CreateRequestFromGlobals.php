<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals;

use Avax\Components\HTTP\Request\System\Capabilities\Body\ParsedBody;
use Avax\Components\HTTP\Request\System\Capabilities\Body\ParseFormBody;
use Avax\Components\HTTP\Request\System\Capabilities\Body\ParseJsonBody;
use Avax\Components\HTTP\Request\System\Capabilities\Body\RawBody;
use Avax\Components\HTTP\Request\System\Capabilities\Body\RequestBody;
use Avax\Components\HTTP\Request\System\Capabilities\Files\NormalizeUploadedFiles;
use Avax\Components\HTTP\Request\System\Capabilities\Files\UploadedFiles;
use Avax\Components\HTTP\Request\System\Capabilities\Headers\NormalizeHeaders;
use Avax\Components\HTTP\Request\System\Capabilities\Headers\RequestHeaders;
use Avax\Components\HTTP\Request\System\Capabilities\Uri\RequestUri;
use Avax\Components\HTTP\Request\System\PublicSurface\Request;
use Exception;

final readonly class CreateRequestFromGlobals
{
    public function __construct(
        private ReadServerParameters   $readServerParameters,
        private ReadQueryParameters    $readQueryParameters,
        private ReadUploadedFiles      $readUploadedFiles,
        private ReadRequestBody        $readRequestBody,
        private NormalizeHeaders       $normalizeHeaders,
        private NormalizeUploadedFiles $normalizeUploadedFiles,
        private ParseJsonBody          $parseJsonBody,
        private ParseFormBody          $parseFormBody,
    ) {
    }

    public function execute(): Request
    {
        try {
            $server  = $this->readServerParameters->read();
            $query   = $this->readQueryParameters->read();
            $files   = $this->readUploadedFiles->read();
            $rawBody = $this->readRequestBody->read();

            $requestHeaders = new RequestHeaders($this->normalizeHeaders->normalize($server));

            $contentType = $requestHeaders->get('Content-Type')?->first() ?? '';
            $parsedData  = match (true) {
                str_contains($contentType, 'application/json')                  => $this->parseJsonBody->parse($rawBody),
                str_contains($contentType, 'application/x-www-form-urlencoded') => $this->parseFormBody->parse($rawBody),
                default                                                         => $_POST
            };

            $requestBody   = new RequestBody(new RawBody($rawBody), new ParsedBody($parsedData));
            $uploadedFiles = new UploadedFiles($this->normalizeUploadedFiles->normalize($files));

            $requestUri = new RequestUri(
                scheme: ($server['HTTPS'] ?? '') === 'on' ? 'https' : 'http',
                host: $server['HTTP_HOST'] ?? 'localhost',
                path: explode('?', $server['REQUEST_URI'] ?? '/')[0],
                query : $server['QUERY_STRING'] ?? '',
            );

            return new Request(
                method: $server['REQUEST_METHOD'] ?? 'GET',
                serverParams: $server,
                cookieParams: $_COOKIE,
                queryParams: $query,
                uri: $requestUri,
                headers: $requestHeaders,
                body: $requestBody,
                files: $uploadedFiles,
            );
        } catch (Exception $exception) {
            throw new GlobalsRequestCreationFailed('Failed to create request from globals: ' . $exception->getMessage(), 0, $exception);
        }
    }
}
