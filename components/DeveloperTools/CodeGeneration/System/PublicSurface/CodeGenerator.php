<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\CodeGeneration\System\PublicSurface;

final readonly class CodeGenerator
{
    /**
     * @param array<string, string> $variables
     */
    public function generate(string $template, array $variables) : string
    {
        return str_replace(
            array_map(fn ($k) => '{{' . $k . '}}', array_keys($variables)),
            array_values($variables),
            $template,
        );
    }
}
