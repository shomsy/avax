<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\Capabilities\Transform;

use Avax\Components\Application\Text\System\PublicSurface\Text;

final class TransformToUpper
{
    public function __invoke(Text $text): Text
    {
        $value = $text->toString();
        if (function_exists('mb_strtoupper')) {
            return Text::of(mb_strtoupper($value, 'UTF-8'));
        }

        return Text::of(strtoupper($value));
    }
}
