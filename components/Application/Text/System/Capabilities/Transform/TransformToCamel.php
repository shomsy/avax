<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\Capabilities\Transform;

use Avax\Components\Application\Text\System\PublicSurface\Text;

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