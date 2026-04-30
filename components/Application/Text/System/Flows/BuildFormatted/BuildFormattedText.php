<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\Flows\BuildFormatted;

final class BuildFormattedText
{
    public static function execute(string $template, array $data = []) : string
    {
        foreach ($data as $key => $value) {
            $template = str_replace(sprintf('{%s}', $key), (string) $value, $template);
        }

        return $template;
    }
}
