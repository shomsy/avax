<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Capabilities;

use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitIssue;

/**
 * Check Forbidden Words
 *
 * Detects forbidden words that violate architectural standards.
 */
final class CheckForbiddenWords implements CheckInterface
{
    /** @var array<string> */
    private array $forbiddenWords
        = [
            'Services', 'Helpers', 'Utils', 'Common', 'Shared',
            'Managers', 'Core', 'Support', 'Misc', 'Stuff',
            'Base', 'Generic', 'Manager', 'Helper', 'Util',
        ];

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

            $content = file_get_contents($filePath);
            if ($content === false) {
                continue;
            }

            foreach ($this->forbiddenWords as $forbiddenWord) {
                // Check in class names implements CheckInterface
                if (preg_match_all('/class\s+(\w*'.preg_quote($forbiddenWord, '/').'\w*)/i', $content, $matches)) {
                    foreach ($matches[1] as $className) {
                        $issues[] = new PreCommitIssue(
                            'CheckForbiddenWords',
                            PreCommitIssue::SEVERITY_ERROR,
                            sprintf("Class name contains forbidden word '%s': %s", $forbiddenWord, $className),
                            $file,
                            null,
                            'FORBIDDEN_WORD_CLASS'
                        );
                    }
                }

                // Check in folder names in path
                if (str_contains((string) $file, $forbiddenWord)) {
                    $issues[] = new PreCommitIssue(
                        'CheckForbiddenWords',
                        PreCommitIssue::SEVERITY_ERROR,
                        sprintf("Path contains forbidden word '%s'", $forbiddenWord),
                        $file,
                        null,
                        'FORBIDDEN_WORD_PATH'
                    );
                }
            }
        }

        return $issues;
    }
}
