<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Integrations\Release;

use FilesystemIterator;
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
     *     forbidden_directories:list<string>,
     *     unexpected_flow_top_level:list<string>,
     *     missing_flow_top_level:list<string>,
     *     unexpected_capability_top_level:list<string>,
     *     missing_capability_top_level:list<string>
     * }
     */
    public function execute(string $repositoryRoot) : array
    {
        $systemRoot = rtrim(string: $repositoryRoot, characters: DIRECTORY_SEPARATOR) . '/System';
        $issues     = [];

        if (! is_dir(filename: $systemRoot)) {
            return [
                'approved'                        => false,
                'issues'                          => ['Missing canonical system root: System/'],
                'unexpected_top_level'            => [],
                'forbidden_directories'           => [],
                'unexpected_flow_top_level'       => [],
                'missing_flow_top_level'          => [],
                'unexpected_capability_top_level' => [],
                'missing_capability_top_level'    => [],
            ];
        }

        $allowedTopLevelDirectories = ['Capabilities', 'Flows', 'Configuration', 'Foundation'];
        $allowedTopLevelFiles       = ['Auth.php', 'DefaultAuth.php'];
        $unexpectedTopLevel         = [];

        $entries = scandir(directory: $systemRoot);

        foreach ($entries === false ? [] : $entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $fullPath = $systemRoot . '/' . $entry;

            if (is_dir(filename: $fullPath) && ! in_array(needle: $entry, haystack: $allowedTopLevelDirectories, strict: true)) {
                $unexpectedTopLevel[] = 'System/' . $entry;
                continue;
            }

            if (is_file(filename: $fullPath) && ! in_array(needle: $entry, haystack: $allowedTopLevelFiles, strict: true)) {
                $unexpectedTopLevel[] = 'System/' . $entry;
            }
        }

        $forbiddenNames       = [
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
            iterator: new RecursiveDirectoryIterator(directory: $systemRoot, flags: FilesystemIterator::SKIP_DOTS),
            mode    : RecursiveIteratorIterator::SELF_FIRST
        );

        /** @var SplFileInfo $node */
        foreach ($iterator as $node) {
            if (! $node->isDir()) {
                continue;
            }

            $name = $node->getBasename();

            if (! in_array(needle: $name, haystack: $forbiddenNames, strict: true)) {
                continue;
            }

            $relativePath           = str_replace(search: $repositoryRoot . '/', replace: '', subject: $node->getPathname());
            $forbiddenDirectories[] = $relativePath;
        }

        sort(array: $unexpectedTopLevel);
        sort(array: $forbiddenDirectories);

        if ($unexpectedTopLevel !== []) {
            $issues[] = 'Unexpected System root entries: ' . implode(separator: ', ', array: $unexpectedTopLevel);
        }

        $flowTopLevel = $this->validateCanonicalChildDirectories(
            root          : $systemRoot . '/Flows',
            expected      : [
                                'ChangeEmail',
                                'ChangePassword',
                                'CheckAuthentication',
                                'Login',
                                'Logout',
                                'RecoverAccess',
                                'Register',
                                'VerifyIdentity',
                            ],
            relativePrefix: 'System/Flows/'
        );

        if ($flowTopLevel['unexpected'] !== []) {
            $issues[] = 'Unexpected System/Flows root entries: ' . implode(separator: ', ', array: $flowTopLevel['unexpected']);
        }

        if ($flowTopLevel['missing'] !== []) {
            $issues[] = 'Missing System/Flows root entries: ' . implode(separator: ', ', array: $flowTopLevel['missing']);
        }

        $capabilityTopLevel = $this->validateCanonicalChildDirectories(
            root          : $systemRoot . '/Capabilities',
            expected      : [
                                'Access',
                                'Diagnostics',
                                'ExternalIdentity',
                                'Identity',
                                'IdentitySync',
                                'Tenancy',
                            ],
            relativePrefix: 'System/Capabilities/'
        );

        if ($capabilityTopLevel['unexpected'] !== []) {
            $issues[] = 'Unexpected System/Capabilities root entries: ' . implode(separator: ', ', array: $capabilityTopLevel['unexpected']);
        }

        if ($capabilityTopLevel['missing'] !== []) {
            $issues[] = 'Missing System/Capabilities root entries: ' . implode(separator: ', ', array: $capabilityTopLevel['missing']);
        }

        if ($forbiddenDirectories !== []) {
            $issues[] = 'Forbidden junk-drawer directories present: ' . implode(separator: ', ', array: $forbiddenDirectories);
        }

        return [
            'approved'                        => $issues === [],
            'issues'                          => $issues,
            'unexpected_top_level'            => $unexpectedTopLevel,
            'forbidden_directories'           => $forbiddenDirectories,
            'unexpected_flow_top_level'       => $flowTopLevel['unexpected'],
            'missing_flow_top_level'          => $flowTopLevel['missing'],
            'unexpected_capability_top_level' => $capabilityTopLevel['unexpected'],
            'missing_capability_top_level'    => $capabilityTopLevel['missing'],
        ];
    }

    /**
     * @param list<string> $expected
     *
     * @return array{unexpected:list<string>, missing:list<string>}
     */
    private function validateCanonicalChildDirectories(
        string $root,
        array  $expected,
        string $relativePrefix
    ) : array
    {
        if (! is_dir(filename: $root)) {
            return [
                'unexpected' => [],
                'missing'    => array_map(
                    callback: static fn (string $directory) : string => $relativePrefix . $directory,
                    array   : $expected
                ),
            ];
        }

        $entries = scandir(directory: $root);
        $actual  = [];

        foreach ($entries === false ? [] : $entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            if (is_dir(filename: $root . '/' . $entry)) {
                $actual[] = $entry;
            }
        }

        sort(array: $actual);

        $unexpected = array_values(array: array_diff($actual, $expected));
        $missing    = array_values(array: array_diff($expected, $actual));

        return [
            'unexpected' => array_map(
                callback: static fn (string $directory) : string => $relativePrefix . $directory,
                array   : $unexpected
            ),
            'missing'    => array_map(
                callback: static fn (string $directory) : string => $relativePrefix . $directory,
                array   : $missing
            ),
        ];
    }
}
