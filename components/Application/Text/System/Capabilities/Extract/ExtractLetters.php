<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\Capabilities\Extract;

use Avax\Components\Application\Text\System\PublicSurface\Text;

final class ExtractLetters
{
    public function __invoke(Text $text): Text
    {
        return new Text(preg_replace('/[^a-zA-Z]/', '', $text->toString()));
    }
}
