<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Capabilities;

use Avax\Framework\System\Capabilities\PreCommit\Configuration\PreCommitConfig;
use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitIssue;

/**
 * Detect Legacy Aliases
 *
 * Detects class_alias usage and compat.php patterns.
 * WARNING - does not auto-delete, only reports.
 */
final class DetectLegacyAliases
{
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
            if (! str_ends_with(strtolower((string) $file), '.php')) {
                continue;
            }

            $filePath = $basePath . '/' . $file;
            if (! file_exists($filePath)) {
                continue;
            }

            $content = file_get_contents($filePath);
            if ($content === false) {
                continue;
            }

            // Check for class_alias usage
            if (preg_match_all('/class_alias\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,/', $content, $matches)) {
                foreach ($matches[1] as $alias) {
                    $issues[] = new PreCommitIssue(
                        'DetectLegacyAliases',
                        PreCommitIssue::SEVERITY_WARNING,
                        sprintf("Uses class_alias to '%s' (legacy pattern)", $alias),
                        $file,
                        null,
                        'LEGACY_ALIAS'
                    );
                }
            }

            // Check for deprecated require/include patterns
            if (preg_match_all('/require(?:_once)?\s*\(\s*[\'"].*(?:legacy|compat|deprecated).*[\'"]/', $content, $matches)) {
                foreach ($matches[0] as $match) {
                    $issues[] = new PreCommitIssue(
                        'DetectLegacyAliases',
                        PreCommitIssue::SEVERITY_WARNING,
                        'Uses legacy require: ' . $match,
                        $file,
                        null,
                        'LEGACY_REQUIRE'
                    );
                }
            }
        }

        // Check compat.php for aliases (global)
        $compatPath = $basePath . '/components/compat.php';
        if (file_exists($compatPath)) {
            $content = file_get_contents($compatPath);
            if ($content !== false && preg_match_all('/class_alias\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,/', $content, $matches)) {
                foreach ($matches[1] as $alias) {
                    $issues[] = new PreCommitIssue(
                        'DetectLegacyAliases',
                        PreCommitIssue::SEVERITY_WARNING,
                        sprintf("compat.php has class_alias to '%s' (should be removed)", $alias),
                        'components/compat.php',
                        null,
                        'LEGACY_COMPAT_ALIAS'
                    );
                }
            }
        }

        return $issues;
    }
}
