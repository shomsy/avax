<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\Capabilities\Transform;

use Avax\Components\Application\Text\System\PublicSurface\Text;

final class TransformLimit
{
    public function __invoke(Text $text, int $max, string $suffix = '…'): Text
    {
        if ($max <= 0) {
            return Text::of('');
        }

        $len = $text->length();
        if ($len <= $max) {
            return $text;
        }

        $cut = function_exists('mb_substr')
            ? mb_substr($text->toString(), 0, $max, 'UTF-8')
            : substr($text->toString(), 0, $max);

        return Text::of($cut.$suffix);
    }
}
