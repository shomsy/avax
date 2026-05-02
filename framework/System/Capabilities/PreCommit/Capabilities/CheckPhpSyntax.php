<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Capabilities;

use Avax\Framework\System\Capabilities\PreCommit\Configuration\PreCommitConfig;
use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitIssue;

/**
 * Check PHP Syntax
 *
 * Validates PHP syntax for staged files.
 */
final class CheckPhpSyntax implements CheckInterface
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

            // PHP lint check
            $output    = [];
            $returnVar = 0;
            exec('php -l ' . escapeshellarg($filePath) . ' 2>&1', $output, $returnVar);

            if ($returnVar !== 0) {
                $issues[] = new PreCommitIssue(
                    'CheckPhpSyntax',
                    PreCommitIssue::SEVERITY_ERROR,
                    'PHP syntax error: ' . implode(' ', $output),
                    $file,
                    null,
                    'PHP_SYNTAX_ERROR'
                );
            }
        }

        return $issues;
    }
}
