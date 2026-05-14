<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\CodeGeneration\System\Flows\GenerateCode;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;

final readonly class GenerateCode
{
    public function __construct(
        private Filesystem $filesystem,
    ) {}

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

        return $this->filesystem->write($outputPath, $code);
    }
}
