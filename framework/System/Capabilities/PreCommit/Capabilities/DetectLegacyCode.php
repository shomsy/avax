<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Capabilities;

use Avax\Framework\System\Capabilities\PreCommit\Configuration\PreCommitConfig;
use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitIssue;

/**
 * Detect Legacy Code
 *
 * Detects legacy code patterns that should be migrated.
 * WARNING - does not auto-delete, only reports.
 */
final class DetectLegacyCode
{
    /** @var array<string> */
    private array $legacyFolders
        = [
            'DataFoundation',
            'components/Legacy',
            'legacy',
            'Deprecated',
            'Old',
        ];

    /** @var array<string, string> */
    private array $legacyPatterns
        = [
            '# Legacy code' => 'contains legacy marker',
            '@legacy'       => 'has @legacy annotation',
        ];

    /**
     * @param array<string, mixed> $context
     *
     * @return list<PreCommitIssue>
     */
    public function run(array $context) : array
    {
        $issues   = [];
        $files    = $context['files'] ?? [];
        $basePath = $context['base_path'] ?? getcwd();

        foreach ($files as $file) {
            $filePath = $basePath . '/' . $file;
            if (! file_exists($filePath)) {
                continue;
            }

            // Check for legacy folder patterns in path
            foreach ($this->legacyFolders as $legacyFolder) {
                if (str_contains($filePath, '/' . $legacyFolder . '/') ||
                    str_contains($filePath, '/' . $legacyFolder . '\\')) {
                    $issues[] = new PreCommitIssue(
                        'DetectLegacyCode',
                        PreCommitIssue::SEVERITY_WARNING,
                        sprintf("File is in legacy folder '%s'", $legacyFolder),
                        $file,
                        null,
                        'LEGACY_FOLDER'
                    );
                }
            }

            // Check content for legacy patterns
            if (preg_match('/\.(php|js|ts|py|go|sh)$/', (string) $file)) {
                $content = file_get_contents($filePath);
                if ($content !== false) {
                    foreach ($this->legacyPatterns as $pattern => $description) {
                        if (stripos($content, $pattern) === false) {
                            continue;
                        }
                        if ($this->isInComment($content, $pattern)) {
                            continue;
                        }
                        $issues[] = new PreCommitIssue(
                            'DetectLegacyCode',
                            PreCommitIssue::SEVERITY_WARNING,
                            'File contains legacy pattern: ' . $description,
                            $file,
                            null,
                            'LEGACY_PATTERN'
                        );
                    }
                }
            }

            // Check for shriomove, tmp folders at project level
            $checkFolders = ['shriomove', '.shriomove', 'tmp', 'Temp'];
            foreach ($checkFolders as $checkFolder) {
                $folderPath = $basePath . '/' . $checkFolder;
                if (is_dir($folderPath) && str_starts_with((string) $file, $checkFolder . '/')) {
                    $issues[] = new PreCommitIssue(
                        'DetectLegacyCode',
                        PreCommitIssue::SEVERITY_WARNING,
                        sprintf("File in temporary folder '%s'", $checkFolder),
                        $file,
                        null,
                        'LEGACY_TEMP_FOLDER'
                    );
                }
            }
        }

        // Check for global legacy folders
        foreach ($this->legacyFolders as $legacyFolder) {
            $folderPath = $basePath . '/components/' . $legacyFolder;
            if (is_dir($folderPath)) {
                $issues[] = new PreCommitIssue(
                    'DetectLegacyCode',
                    PreCommitIssue::SEVERITY_WARNING,
                    'Legacy folder exists: components/' . $legacyFolder,
                    null,
                    null,
                    'LEGACY_FOLDER_EXISTS'
                );
            }
        }

        return $issues;
    }

    private function isInComment(string $content, string $pattern) : bool
    {
        $pos = stripos($content, $pattern);
        if ($pos === false) {
            return false;
        }

        $before = substr($content, 0, $pos);

        // Check if in line comment
        if (str_contains($before, '//') || str_contains($before, '#')) {
            $lastNewline     = strrpos($before, "\n");
            $lastLineComment = strrpos($before, '//');
            if ($lastLineComment !== false && ($lastNewline === false || $lastLineComment > $lastNewline)) {
                return true;
            }
        }

        return false;
    }
}
