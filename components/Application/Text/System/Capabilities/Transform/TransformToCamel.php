<?php

declare(strict_types=1);

namespace Avax\Text\System\Capabilities\Transform;

use Avax\Text\System\PublicSurface\Text;

final class TransformToCamel
{
    public function __invoke(Text $text) : Text
    {
        $studly = $text->studly()->toString();
        if ($studly === '') {
            return new Text('');
        }

        return new Text(lcfirst($studly));
    }
}