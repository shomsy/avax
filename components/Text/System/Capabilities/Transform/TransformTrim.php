<?php

declare(strict_types=1);

namespace Avax\Text\System\Capabilities\Transform;

use Avax\Text\System\PublicSurface\Text;

final class TransformTrim
{
    public function __invoke(Text $text, string $chars = " \t\n\r\0\x0B") : Text
    {
        return new Text(trim($text->toString(), $chars));
    }
}