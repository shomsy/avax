<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Capabilities;

use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitIssue;

/**
 * Check PublicSurface Rules
 *
 * Validates classes in System/PublicSurface are thin and delegate.
 */
final class CheckPublicSurfaceRules implements CheckInterface
{
    /**
     * @param  array<string, mixed>  $context
     * @return list<PreCommitIssue>
     */
    public function run(array $context): array
    {
        $issues = [];
        $files = $context['files'] ?? [];
        $basePath = $context['base_path'] ?? getcwd();

        foreach ($files as $file) {
            $filePath = $basePath.'/'.$file;
            if (! file_exists($filePath)) {
                continue;
            }

            // Only check files in PublicSurface
            if (! str_contains($filePath, '/System/PublicSurface/') &&
                ! str_contains($filePath, '/PublicSurface/')) {
                continue;
            }

            if (! str_ends_with(strtolower((string) $file), '.php')) {
                continue;
            }

            $content = file_get_contents($filePath);
            if ($content === false) {
                continue;
            }

            // Check for business logic patterns (loops)
            $businessPatterns = [' foreach', ' while', ' for(', ' switch(', ' match('];
            foreach ($businessPatterns as $businessPattern) {
                if (str_contains($content, $businessPattern)) {
                    $issues[] = new PreCommitIssue(
                        'CheckPublicSurfaceRules',
                        PreCommitIssue::SEVERITY_ERROR,
                        sprintf('Potential business logic (%s) in PublicSurface. Should delegate to Flows/Capabilities.', $businessPattern),
                        $file,
                        null,
                        'SURFACE_LOGIC'
                    );
                }
            }

            // Check file size (> 150 lines is too much for thin facade)
            $lines = substr_count($content, "\n");
            if ($lines > 150) {
                $issues[] = new PreCommitIssue(
                    'CheckPublicSurfaceRules',
                    PreCommitIssue::SEVERITY_WARNING,
                    sprintf('PublicSurface file is large (%d lines). May contain business logic.', $lines),
                    $file,
                    null,
                    'SURFACE_SIZE'
                );
            }
        }

        return $issues;
    }
}
