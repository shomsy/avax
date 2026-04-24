<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Flows\BuildResponse;

use Avax\HTTP\Response\Capabilities\Downloads\BuildAttachmentDisposition;
use Avax\HTTP\Response\Capabilities\Downloads\DetectDownloadMediaType;
use Avax\HTTP\Response\Capabilities\Streams\ResponseStreamFactory;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class BuildFileDownloadResponse
{
    public function __invoke(string $filePath, string|null $downloadName = null, int $status = 200, array $headers = []) : ResponseInterface
    {
        if (! is_file(filename: $filePath) || ! is_readable(filename: $filePath)) {
            throw new RuntimeException(message: "Download file [{$filePath}] does not exist or is not readable.");
        }

        $downloadName ??= basename(path: $filePath);

        return (new BuildResponse())(
            status : $status,
            headers: [
                         'Content-Type'        => (new DetectDownloadMediaType())($filePath),
                         'Content-Disposition' => (new BuildAttachmentDisposition())($downloadName),
                         'Content-Length'      => (string) filesize(filename: $filePath),
                         ...$headers,
                     ],
            body   : (new ResponseStreamFactory())->openFileStream(path: $filePath),
        );
    }
}
