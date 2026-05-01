<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Capabilities;

use Avax\Framework\System\Capabilities\PreCommit\Configuration\PreCommitConfig;
use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitIssue;

/**
 * Detect TODO Comments
 *
 * Detects TODO, FIXME, NOTE, HACK comments.
 * WARNING - does not auto-delete, only reports.
 */
final class DetectTodoComments
{
    private PreCommitConfig $config;

    public function __construct(PreCommitConfig $config)
    {
        $this->config = $config;
    }

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
            if (! str_ends_with(strtolower($file), '.php')) {
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

            // Check for TODO/FIXME/NOTE/HACK (excluding our own .agents dir)
            if (strpos($file, '.agents') === false && strpos($file, '/tests/') === false) {
                if (stripos($content, 'TODO') !== false ||
                    stripos($content, 'FIXME') !== false ||
                    stripos($content, 'NOTE:') !== false ||
                    stripos($content, 'HACK') !== false) {
                    $issues[] = new PreCommitIssue(
                        'DetectTodoComments',
                        PreCommitIssue::SEVERITY_WARNING,
                        'File contains TODO/FIXME/NOTE/HACK comments',
                        $file,
                        null,
                        'TODO_COMMENT'
                    );
                }
            }
        }

        return $issues;
    }
}
