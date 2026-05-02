<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Capabilities;

use Avax\Framework\System\Capabilities\PreCommit\Configuration\PreCommitConfig;
use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitIssue;

/**
 * Detect Architecture Violations
 *
 * Runs external tooling scripts to detect architecture issues.
 * Uses check-*.php scripts from tooling/architecture/
 */
final class DetectArchitectureViolations implements CheckInterface
{
    /** @var array<string, string> */
    private array $scripts
        = [
            'check-forbidden-folders' => 'CheckForbiddenFolders',
            'check-public-surface'    => 'CheckPublicSurface',
            'check-namespace-drift'   => 'CheckNamespaceDrift',
        ];

    /**
     * @param array<string, mixed> $context
     *
     * @return list<PreCommitIssue>
     */
    public function run(array $context) : array
    {
        $issues      = [];
        $basePath    = getcwd();
        $toolingPath = $basePath . '/tooling/architecture';

        if (! is_dir($toolingPath)) {
            return $issues;
        }

        foreach ($this->scripts as $scriptName => $checkName) {
            $scriptPath = $toolingPath . '/' . $scriptName . '.php';

            if (! file_exists($scriptPath)) {
                continue;
            }

            // Run the script
            $output    = [];
            $returnVar = 0;
            exec('php ' . escapeshellarg($scriptPath) . ' 2>&1', $output, $returnVar);
            $outputText = implode("\n", $output);

            if ($returnVar !== 0) {
                $issues[] = new PreCommitIssue(
                    'DetectArchitectureViolations',
                    PreCommitIssue::SEVERITY_ERROR,
                    sprintf("Architecture check '%s' failed: ", $checkName) . substr($outputText, 0, 200),
                    null,
                    null,
                    'ARCH_' . strtoupper(substr($scriptName, 6))
                );
            }
        }

        return $issues;
    }
}
