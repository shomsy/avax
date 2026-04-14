<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Release;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final readonly class CheckSystemShape
{
    /**
     * @return array{
     *     approved:bool,
     *     issues:list<string>,
     *     unexpected_top_level:list<string>,
     *     forbidden_directories:list<string>
     * }
     */
    public function execute(string $repositoryRoot) : array
    {
        $systemRoot = rtrim($repositoryRoot, DIRECTORY_SEPARATOR) . '/System';
        $issues = [];

        if (! is_dir($systemRoot)) {
            return [
                'approved' => false,
                'issues' => ['Missing canonical system root: System/'],
                'unexpected_top_level' => [],
                'forbidden_directories' => [],
            ];
        }

        $allowedTopLevelDirectories = ['Capability', 'Flow', 'Configuration', 'Foundation'];
        $allowedTopLevelFiles = ['Auth.php', 'AuthInterface.php'];
        $unexpectedTopLevel = [];

        $entries = scandir($systemRoot);

        foreach ($entries === false ? [] : $entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $fullPath = $systemRoot . '/' . $entry;

            if (is_dir($fullPath) && ! in_array($entry, $allowedTopLevelDirectories, true)) {
                $unexpectedTopLevel[] = 'System/' . $entry;
                continue;
            }

            if (is_file($fullPath) && ! in_array($entry, $allowedTopLevelFiles, true)) {
                $unexpectedTopLevel[] = 'System/' . $entry;
            }
        }

        $forbiddenNames = [
            'Actions',
            'Adapters',
            'Contracts',
            'Services',
            'Helpers',
            'Utils',
            'Common',
            'Misc',
            'Managers',
            'Stuff',
            'Shared',
            'Base',
            'Core',
            'SharedThings',
            'General',
            'InternalHelpers',
        ];
        $forbiddenDirectories = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($systemRoot, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        /** @var SplFileInfo $node */
        foreach ($iterator as $node) {
            if (! $node->isDir()) {
                continue;
            }

            $name = $node->getBasename();

            if (! in_array($name, $forbiddenNames, true)) {
                continue;
            }

            $relativePath = str_replace($repositoryRoot . '/', '', $node->getPathname());
            $forbiddenDirectories[] = $relativePath;
        }

        sort($unexpectedTopLevel);
        sort($forbiddenDirectories);

        if ($unexpectedTopLevel !== []) {
            $issues[] = 'Unexpected System root entries: ' . implode(', ', $unexpectedTopLevel);
        }

        if ($forbiddenDirectories !== []) {
            $issues[] = 'Forbidden junk-drawer directories present: ' . implode(', ', $forbiddenDirectories);
        }

        return [
            'approved' => $issues === [],
            'issues' => $issues,
            'unexpected_top_level' => $unexpectedTopLevel,
            'forbidden_directories' => $forbiddenDirectories,
        ];
    }
}
