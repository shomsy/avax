<?php

declare(strict_types=1);

namespace Avax\Tooling\Quality;

final class CanonicalTreeDefinition
{
    public const string VERSION = '1.0.0';

    public const string UPDATED = '2026-05-03';

    public static function get(): array
    {
        return [
            'version' => self::VERSION,
            'updated' => self::UPDATED,
            'stages' => [
                'v1_kernel' => [
                    'name' => 'V1 Kernel',
                    'status' => 'active',
                    'roots' => [
                        'AGENTS.md',
                        'CURRENT_TRUTH.md',
                        'README.md',
                        'composer.json',
                        'composer.lock',
                        'phpunit.xml',
                        'phpstan.neon',
                        'rector.php',
                        'bin/',
                        'framework/',
                        'components/',
                        'tests/',
                        'docs/',
                        'examples/',
                        'tooling/',
                        'build/',
                        'EVIDENCE/',
                    ],
                    'required_dirs' => [
                        'framework/System' => [
                            'PublicSurface',
                            'Flows',
                            'Capabilities',
                            'Configuration',
                            'Foundation',
                        ],
                        'components' => [
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
                ],
                'v2_platform' => [
                    'name' => 'V2 Platform',
                    'status' => 'locked',
                    'roots' => [
                        'labs/',
                        'reference-architectures/',
                    ],
                    'allowed_in_labs' => [
                        'API',
                        'Integration',
                        'ObjectStorage',
                        'MessageBroker',
                        'SearchIndex',
                    ],
                    'forbidden_in_components' => [
                        '*', // No V2 in components until V1 is green
                    ],
                ],
                'v3_system_design' => [
                    'name' => 'V3 System Design',
                    'status' => 'locked',
                    'roots' => [
                        'labs/SystemDesignKit/',
                    ],
                    'allowed_in_labs' => [
                        'SystemDesignKit',
                    ],
                ],
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
            'naming_rules' => [
                'folder_represents' => 'flow or capability',
                'file_class_represents' => 'specific responsibility',
                'method_represents' => 'exact action',
            ],
            'required_files' => [
                'AGENTS.md',
                'CURRENT_TRUTH.md',
                'README.md',
                'composer.json',
                'phpunit.xml',
                'phpstan.neon',
            ],
            'optional_files' => [
                'psalm.xml',
                'rector.php',
            ],
        ];
    }

    public static function getV1Components(): array
    {
        return [
            'Application',
            'CLI',
            'DataStack',
            'DeveloperTools',
            'HTTP',
            'Identity',
            'Operations',
            'Presentation',
            'Security',
        ];
    }

    public static function getV2Labs(): array
    {
        return [
            'API',
            'Integration',
        ];
    }

    public static function getExtraAllowedRoots(): array
    {
        return [
            'labs/',
            'benchmarks/',
            'examples/',
            'reference-architectures/',
            'build/',
        ];
    }
}
