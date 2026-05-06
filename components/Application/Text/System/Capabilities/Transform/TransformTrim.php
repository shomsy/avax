<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\Capabilities\Transform;

use Avax\Components\Application\Text\System\PublicSurface\Text;

final class TransformTrim
{
    public function __invoke(Text $text, string $chars = " \t\n\r\0\x0B"): Text
    {
        return Text::of(trim($text->toString(), $chars));
    }
}
