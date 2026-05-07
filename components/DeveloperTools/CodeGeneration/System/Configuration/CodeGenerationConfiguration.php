<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\CodeGeneration\System\Configuration;

final readonly class CodeGenerationConfiguration
{
    public function __construct(
        public string $templateDirectory = 'templates',
        public string $outputDirectory = 'generated',
        public string $defaultNamespace = 'App',
        public bool   $overwriteExisting = false,
    ) {}
}
