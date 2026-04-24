<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Downloads;

final class BuildInlineDisposition
{
    public function __invoke(string $downloadName) : string
    {
        $escaped = addcslashes(string: $downloadName, characters: "\"\\");

        return "inline; filename=\"{$escaped}\"";
    }
}
