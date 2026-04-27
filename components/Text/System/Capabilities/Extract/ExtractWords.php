<?php

declare(strict_types=1);

namespace Avax\Text\System\Capabilities\Extract;

use Avax\Text\System\PublicSurface\Text;

final class ExtractWords
{
    public function __invoke(Text $text) : array
    {
        preg_match_all('/\b\w+\b/', $text->toString(), $matches);

        return array_values(array_filter($matches[0] ?? []));
    }
}