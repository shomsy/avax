<?php

declare(strict_types=1);

namespace Avax\Text\System\Capabilities\Transform;

use Avax\Text\System\PublicSurface\Text;

final class TransformToStudly
{
    public function __invoke(Text $text) : Text
    {
        $s     = preg_replace('/[^a-zA-Z0-9]+/', ' ', $text->toAscii()->toString());
        $s     = preg_replace('/\s+/', ' ', trim($s));
        $parts = explode(' ', $s);
        $parts = array_map(fn (string $p) => $p === '' ? '' : ucfirst(mb_strtolower($p, 'UTF-8')), $parts);

        return new Text(implode('', $parts));
    }
}