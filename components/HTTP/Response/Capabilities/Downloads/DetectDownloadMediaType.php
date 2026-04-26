<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Downloads;

final class DetectDownloadMediaType
{
    public function __invoke(string $path) : string
    {
        $finfo = finfo_open(options: FILEINFO_MIME_TYPE);

        if ($finfo === false) {
            return 'application/octet-stream';
        }

        $mediaType = finfo_file(finfo: $finfo, filename: $path) ?: 'application/octet-stream';
        finfo_close(finfo: $finfo);

        return $mediaType;
    }
}
