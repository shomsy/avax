<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

class AutoloadFixer
{
    public static function fix(): void
    {
        $fixer = new self();
        $fixer->run();
    }

    private function run(): void
    {
        $composer = json_decode(file_get_contents('composer.json'), true);

        $autoload = $composer['autoload'] ?? [];

        $psr4 = $autoload['psr-4'] ?? [];

        $additional = [
            'Avax\\Components\\Tests\\' => 'tests/',
            'Avax\\Tests\\Unit\\Components\\' => 'tests/Unit/Components/',
            'Avax\\Tests\\Feature\\Components\\' => 'tests/Feature/Components/',
        ];

        $merged = array_merge($psr4, $additional);

        $autoload['psr-4'] = $merged;
        $composer['autoload'] = $autoload;

        file_put_contents(
            'composer.json',
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
        );

        echo "Added compatibility autoload paths\n";
    }
}
