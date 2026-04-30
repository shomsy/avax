<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\Capabilities\Transform;

use Avax\Components\Application\Text\System\PublicSurface\Text;

final class TransformToAscii
{
    public function __invoke(Text $text) : Text
    {
        $v = $text->toString();
        if (function_exists('iconv')) {
            $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $v);
            if (is_string($converted)) {
                $v = $converted;
            }
        }

        $v = preg_replace('/[^\x20-\x7E]/', '', $v);

        return new Text(is_string($v) ? $v : $text->toString());
    }
}
