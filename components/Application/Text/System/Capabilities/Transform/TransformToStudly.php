<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\Capabilities\Transform;

use Avax\Components\Application\Text\System\PublicSurface\Text;

final class TransformToStudly
{
    public function __invoke(Text $text): Text
    {
        $s = preg_replace('/[^a-zA-Z0-9]+/', ' ', $text->toAscii()->toString());
        $s = preg_replace('/\s+/', ' ', trim((string) $s));

        $parts = explode(' ', (string) $s);
        $parts = array_map(static fn (string $p): string => $p === '' ? '' : ucfirst(mb_strtolower($p, 'UTF-8')), $parts);

        return Text::of(implode('', $parts));
    }
}
