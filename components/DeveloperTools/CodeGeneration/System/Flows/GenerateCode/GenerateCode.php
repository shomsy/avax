<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\CodeGeneration\System\Flows\GenerateCode;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;

final class GenerateCode
{
    /**
     * @param array<string, string> $variables
     */
    public function generate(string $template, array $variables, string $outputPath, Filesystem|null $filesystem = null) : bool
    {
        $code = str_replace(
            array_map(fn ($k) => '{{' . $k . '}}', array_keys($variables)),
            array_values($variables),
            $template,
        );

        $fs = $filesystem ?? new Filesystem();

        return $fs->write($outputPath, $code);
    }
}
