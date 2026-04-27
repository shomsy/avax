<?php

declare(strict_types=1);

namespace Avax\Text\System\Capabilities\Transform;

use Avax\Text\System\PublicSurface\Text;

final class TransformToSnake
{
    public function __invoke(Text $text, string $delimiter = '_') : Text
    {
        $value = $text->toString();
        $value = preg_replace('/([a-z0-9])([A-Z])/', '$1' . $delimiter . '$2', $value);
        $value = preg_replace('/[\s\-]+/', $delimiter, $value);

        return new Text(strtolower($value));
    }
}