<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\Capabilities\Transform;

use Avax\Components\Application\Text\System\PublicSurface\Text;

final class TransformToSingular
{
    public function __invoke(Text $text) : Text
    {
        $value = $text->toString();
        if (preg_match('/ies$/i', $value)) {
            return new Text(substr($value, 0, -3) . 'y');
        }

        if (preg_match('/es$/i', $value)) {
            return new Text(substr($value, 0, -2));
        }

        if (preg_match('/s$/i', $value)) {
            return new Text(substr($value, 0, -1));
        }

        return new Text($value);
    }
}
