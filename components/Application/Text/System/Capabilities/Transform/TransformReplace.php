<?php

declare(strict_types=1);

namespace Avax\Text\System\Capabilities\Transform;

use Avax\Text\System\PublicSurface\Text;

final class TransformReplace
{
    public function __invoke(Text $text, string $search, string $replace) : Text
    {
        return new Text(str_replace($search, $replace, $text->toString()));
    }
}