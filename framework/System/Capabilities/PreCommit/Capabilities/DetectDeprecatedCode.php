<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Capabilities;

use Avax\Framework\System\Capabilities\PreCommit\Configuration\PreCommitConfig;
use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitIssue;

/**
 * Detect Deprecated Code
 *
 * Detects deprecated methods and patterns.
 * WARNING - does not auto-delete, only reports.
 */
final class DetectDeprecatedCode
{
    private PreCommitConfig $config;

    /** @var array<string, string> */
    private array $deprecatedPatterns
        = [
            '/\$this->validate\(/'    => 'uses $this->validate() (deprecated)',
            '/\$this->validateAll\(/' => 'uses $this->validateAll() (deprecated)',
        ];

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

            foreach ($this->deprecatedPatterns as $pattern => $description) {
                if (preg_match($pattern, $content)) {
                    $issues[] = new PreCommitIssue(
                        'DetectDeprecatedCode',
                        PreCommitIssue::SEVERITY_WARNING,
                        "Deprecated pattern: {$description}",
                        $file,
                        null,
                        'DEPRECATED_PATTERN'
                    );
                }
            }

            // Check for @deprecated annotations
            if (preg_match_all('/@deprecated/i', $content, $matches)) {
                $issues[] = new PreCommitIssue(
                    'DetectDeprecatedCode',
                    PreCommitIssue::SEVERITY_WARNING,
                    'File contains @deprecated annotations (' . count($matches[0]) . ')',
                    $file,
                    null,
                    'DEPRECATED_ANNOTATION'
                );
            }
        }

        return $issues;
    }
}
