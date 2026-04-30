<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\Capabilities\Transform;

use Avax\Components\Application\Text\System\PublicSurface\Text;

final class TransformToSlug
{
    public function __invoke(Text $text) : Text
    {
        $s = $text->toAscii()->lower()->toString();
        $s = preg_replace('/[^a-z0-9]+/', '-', $s);
        $s = trim((string) $s, '-');

        return new Text($s);
    }
}
