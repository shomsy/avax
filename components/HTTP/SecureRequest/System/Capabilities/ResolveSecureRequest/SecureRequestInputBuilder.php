<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\SecureRequest\System\Capabilities\ResolveSecureRequest;

use Avax\Components\HTTP\SecureRequest\System\PublicSurface\SecureRequest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Builds input data for SecureRequest from HTTP request.
 *
 * Merge priority (higher overrides lower):
 * 1. Route params
 * 2. Uploaded files
 * 3. Parsed body (form) or JSON body
 * 4. Query params
 *
 * JSON body is parsed from the raw body stream if getParsedBody()
 * returns null and the Content-Type indicates JSON.
 *
 * Uploaded files are included as arrays of UploadedFileInterface
 * keyed by their field name. Single-file fields return the file directly.
 */
final readonly class SecureRequestInputBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function buildInput(ServerRequestInterface $request) : array
    {
        $input = [];

        // 4. Query parameters (lowest priority)
        $input = [...$input, ...$request->getQueryParams()];

        // 3. Parsed body or JSON body
        $body = $request->getParsedBody();
        if (is_array($body)) {
            $input = [...$input, ...$body];
        } elseif ($body === null) {
            $jsonBody = $this->parseJsonBody($request);
            if ($jsonBody !== null) {
                $input = [...$input, ...$jsonBody];
            }
        }

        // 2. Uploaded files
        $files = $request->getUploadedFiles();
        $input = [...$input, ...$this->normalizeUploadedFiles($files)];

        // 1. Route parameters (highest priority)
        foreach ($request->getAttributes() as $key => $value) {
            if (is_string($key) && $key !== '') {
                $input[$key] = $value;
            }
        }

        return $input;
    }

    /**
     * Parse JSON body from the request stream if Content-Type indicates JSON.
     *
     * @return array<string, mixed>|null
     */
    private function parseJsonBody(ServerRequestInterface $request) : array|null
    {
        $contentType = $request->getHeaderLine('Content-Type');
        if (! str_contains($contentType, 'application/json')) {
            return null;
        }

        $body = (string) $request->getBody();
        if ($body === '') {
            return null;
        }

        $decoded = json_decode($body, true);
        if (! is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    /**
     * Normalize uploaded files into the input array.
     *
     * Single file fields return the UploadedFileInterface directly.
     * Multi-file fields (numeric keys) return arrays of UploadedFileInterface.
     *
     * @param array<string, UploadedFileInterface|UploadedFileInterface[]> $files
     *
     * @return array<string, mixed>
     */
    private function normalizeUploadedFiles(array $files) : array
    {
        $input = [];

        foreach ($files as $key => $file) {
            if ($file instanceof UploadedFileInterface) {
                if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $input[$key] = $file;
            } elseif (is_array($file)) {
                $normalized = $this->normalizeNestedFiles($file);
                if ($normalized !== []) {
                    $input[$key] = $normalized;
                }
            }
        }

        return $input;
    }

    /**
     * Normalize nested file arrays (from multi-file uploads).
     *
     * @param mixed $files
     *
     * @return array<string, mixed>
     */
    private function normalizeNestedFiles(mixed $files) : array
    {
        $result = [];

        foreach ($files as $key => $file) {
            if ($file instanceof UploadedFileInterface) {
                if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $result[$key] = $file;
            } elseif (is_array($file)) {
                $normalized = $this->normalizeNestedFiles($file);
                if ($normalized !== []) {
                    $result[$key] = $normalized;
                }
            }
        }

        return $result;
    }
}
