<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\CodeGeneration\System\Flows\RenderTemplate;

final readonly class RenderTemplate
{
    /**
     * @param array<string, string> $variables
     */
    public function render(string $template, array $variables) : string
    {
        return str_replace(
            array_map(fn ($k) => '{{' . $k . '}}', array_keys($variables)),
            array_values($variables),
            $template,
        );
    }
}
