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

final class CreateRequestFromGlobals
{
    public function __construct(
        private ReadServerParameters $serverReader,
        private ReadQueryParameters $queryReader,
        private ReadUploadedFiles $fileReader,
        private ReadRequestBody $bodyReader,
        private NormalizeHeaders $headerNormalizer,
        private NormalizeUploadedFiles $fileNormalizer,
        private ParseJsonBody $jsonParser,
        private ParseFormBody $formParser,
    ) {}

    public function execute() : Request
    {
        try {
            $server     = $this->serverReader->read();
            $query      = $this->queryReader->read();
            $files      = $this->fileReader->read();
            $rawBody = $this->bodyReader->read();

            $headers = new RequestHeaders($this->headerNormalizer->normalize($server));

            $contentType = $headers->get('Content-Type')?->first() ?? '';
            $parsedData = match (true) {
                str_contains($contentType, 'application/json') => $this->jsonParser->parse($rawBody),
                str_contains($contentType, 'application/x-www-form-urlencoded') => $this->formParser->parse($rawBody),
                default                                        => $_POST
            };

            $body = new RequestBody(new RawBody($rawBody), new ParsedBody($parsedData));
            $uploadedFiles = new UploadedFiles($this->fileNormalizer->normalize($files));

            $uri = new RequestUri(
                scheme: ($server['HTTPS'] ?? '') === 'on' ? 'https' : 'http',
                host: $server['HTTP_HOST'] ?? 'localhost',
                path: explode('?', $server['REQUEST_URI'] ?? '/')[0],
                query : $server['QUERY_STRING'] ?? '',
            );

            return new Request(
                method: $server['REQUEST_METHOD'] ?? 'GET',
                uri: $uri,
                headers: $headers,
                body: $body,
                files: $uploadedFiles,
                serverParams: $server,
                cookieParams: $_COOKIE,
                queryParams: $query,
            );
        } catch (Exception $e) {
            throw new GlobalsRequestCreationFailed('Failed to create request from globals: ' . $e->getMessage(), 0, $e);
        }
    }
}
