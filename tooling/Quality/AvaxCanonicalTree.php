<?php

declare(strict_types=1);

namespace Avax\Tooling\Quality;

class AvaxCanonicalTree
{
    public const VERSION = '1.0.0';

    public const UPDATED = '2026-05-03';

    public const STAGE = 'Stage 02: Taxonomy resolved, Stage 06 active';

    public const KNOWN_ISSUES
        = [
            'autoload_skips' => 280,
            'production_files_affected' => 66,
            'main_problem' => 'multi-class files in components/ need splitting',
        ];

    public static function getV1Components(): array
    {
        return self::get()['v1_components'];
    }

    public static function get(): array
    {
        return [
            'version' => self::VERSION,
            'updated' => self::UPDATED,
            'v1_components' => [
                'Application',
                'CLI',
                'DataStack',
                'DeveloperTools',
                'HTTP',
                'Identity',
                'Operations',
                'Presentation',
                'Security',
            ],
            'forbidden_in_components' => [
                'API',
                'Integration',
                'DependencyMap',
                'Documentation',
                'Performance',
                'Server',
                '.idea',
                'DataLayer',
            ],
            'forbidden_folder_names' => [
                'Services',
                'Helpers',
                'Utils',
                'Common',
                'Shared',
                'Managers',
                'Core',
                'Support',
            ],
            'required_files' => [
                'AGENTS.md',
                'CURRENT_TRUTH.md',
                'README.md',
                'composer.json',
                'phpunit.xml',
                'phpstan.neon',
            ],
            'v2_labs' => ['API', 'Integration'],
        ];
    }

    public static function getV2Labs(): array
    {
        return self::get()['v2_labs'];
    }

    public static function getExtraRoots(): array
    {
        return ['labs', 'benchmarks', 'examples', 'reference-architectures', 'build'];
    }

    public static function getRequiredFiles(): array
    {
        return self::get()['required_files'];
    }

    public static function getForbiddenFolders(): array
    {
        return self::get()['forbidden_folder_names'];
    }

    public static function getForbiddenInComponents(): array
    {
        return self::get()['forbidden_in_components'];
    }
}
