<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Capabilities;

use Avax\Framework\System\Capabilities\PreCommit\Configuration\PreCommitConfig;
use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitIssue;

/**
 * Detect Tooling Scripts
 *
 * Detects and validates tooling scripts follow conventions.
 */
final class DetectToolingScripts
{
    private PreCommitConfig $config;

    public function __construct(PreCommitConfig $config)
    {
        $this->config = $config;
    }

    public function run(array $context) : array
    {
        $issues   = [];
        $files    = $context['files'] ?? [];
        $basePath = $context['base_path'] ?? getcwd();

        foreach ($files as $file) {
            // Check tooling directory files
            if (! str_starts_with($file, 'tooling/')) {
                continue;
            }

            $filePath = $basePath . '/' . $file;
            if (! file_exists($filePath)) {
                continue;
            }

            // Shell scripts should have proper shebang
            if (str_ends_with($file, '.sh')) {
                $content = file_get_contents($filePath);
                if ($content !== false && ! str_starts_with(trim($content), '#!/')) {
                    $issues[] = new PreCommitIssue(
                        'DetectToolingScripts',
                        PreCommitIssue::SEVERITY_INFO,
                        "Shell script missing shebang: {$file}",
                        $file,
                        null,
                        'TOOLING_SHEBANG'
                    );
                }
            }

            // Check for executable permission on shell scripts
            if (str_ends_with($file, '.sh') && is_file($filePath)) {
                $perms = fileperms($filePath);
                if ($perms !== false && ! ($perms & 0x0040)) { // Check if group executable
                    $issues[] = new PreCommitIssue(
                        'DetectToolingScripts',
                        PreCommitIssue::SEVERITY_INFO,
                        "Shell script may need executable permission: {$file}",
                        $file,
                        null,
                        'TOOLING_PERM'
                    );
                }
            }
        }

        return $issues;
    }
}
