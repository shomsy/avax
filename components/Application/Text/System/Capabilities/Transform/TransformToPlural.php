<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\Capabilities\Transform;

use Avax\Components\Application\Text\System\PublicSurface\Text;

final class TransformToPlural
{
    public function __invoke(Text $text) : Text
    {
        $value = $text->toString();
        if (preg_match('/[sxz]$|sh$|ch$/i', $value)) {
            return new Text($value . 'es');
        }
        if (preg_match('/([^aeiou])y$/i', $value)) {
            return new Text(substr($value, 0, -1) . 'ies');
        }

        return new Text($value . 's');
    }
}