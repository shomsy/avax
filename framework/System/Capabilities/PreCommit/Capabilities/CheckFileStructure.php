<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Capabilities;

use Avax\Framework\System\Capabilities\PreCommit\Configuration\PreCommitConfig;
use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitIssue;

/**
 * Check File Structure
 *
 * Validates file/folder structure follows architectural pattern.
 */
final class CheckFileStructure
{
    /** @var array<string> */
    private array $forbiddenTopLevel
        = [
            'Services', 'Helpers', 'Utils', 'Common', 'Shared',
            'Managers', 'Core', 'Misc', 'Generic',
        ];

    /**
     * @param array<string, mixed> $context
     *
     * @return list<PreCommitIssue>
     */
    public function run(array $context) : array
    {
        $issues     = [];
        $files      = $context['files'] ?? [];
        $basePath   = $context['base_path'] ?? getcwd();
        $systemRoot = $context['system_root'] ?? $basePath;

        foreach ($files as $file) {
            $filePath = $basePath . '/' . $file;
            if (! file_exists($filePath)) {
                continue;
            }

            // Check top-level folders
            $relativePath = str_replace($systemRoot . '/', '', $filePath);
            $parts        = explode('/', $relativePath);

            if (count($parts) >= 2) {
                $topFolder = $parts[0];

                foreach ($this->forbiddenTopLevel as $forbidden) {
                    if (strcasecmp($topFolder, $forbidden) === 0) {
                        $issues[] = new PreCommitIssue(
                            'CheckFileStructure',
                            PreCommitIssue::SEVERITY_ERROR,
                            sprintf("Forbidden top-level folder '%s' in path. Use Flows/, Capabilities/, or specific domain folders.", $forbidden),
                            $file,
                            null,
                            'STRUCTURE_TOPLEVEL'
                        );
                    }
                }
            }

            // Check depth warning
            $depth = count($parts) - 1;
            if ($depth > 4) {
                $issues[] = new PreCommitIssue(
                    'CheckFileStructure',
                    PreCommitIssue::SEVERITY_WARNING,
                    sprintf('Deep nesting (depth %d) in %s. Consider flattening.', $depth, $file),
                    $file,
                    null,
                    'STRUCTURE_DEPTH'
                );
            }
        }

        return $issues;
    }
}
