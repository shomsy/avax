<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\CodeGeneration\System\Flows\GenerateCode;

final readonly class GenerateCode
{
    /**
     * @param array<string, string> $variables
     */
    public function generate(string $template, array $variables, string $outputPath) : bool
    {
        $code = str_replace(
            array_map(fn ($k) => '{{' . $k . '}}', array_keys($variables)),
            array_values($variables),
            $template,
        );

        return file_put_contents($outputPath, $code) !== false;
    }
}
