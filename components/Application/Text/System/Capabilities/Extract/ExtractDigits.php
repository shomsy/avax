<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\Capabilities\Extract;

use Avax\Components\Application\Text\System\PublicSurface\Text;

final class ExtractDigits
{
    public function __invoke(Text $text): Text
    {
        return Text::of(preg_replace('/\D/', '', $text->toString()));
    }
}
