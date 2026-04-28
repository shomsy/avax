<?php

declare(strict_types=1);

namespace Avax\Text\System\Capabilities\Transform;

use Avax\Text\System\PublicSurface\Text;

final class TransformToLower
{
    public function __invoke(Text $text) : Text
    {
        $value = $text->toString();
        if (function_exists('mb_strtolower')) {
            return new Text(mb_strtolower($value, 'UTF-8'));
        }

        return new Text(strtolower($value));
    }
}