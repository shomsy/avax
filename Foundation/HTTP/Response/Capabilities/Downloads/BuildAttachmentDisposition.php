<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Downloads;

final class BuildAttachmentDisposition
{
    public function __invoke(string $downloadName) : string
    {
        $escaped = addcslashes(string: $downloadName, characters: "\"\\");

        return "attachment; filename=\"{$escaped}\"";
    }
}
