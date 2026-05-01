<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Capabilities;

use Avax\Framework\System\Capabilities\PreCommit\Configuration\PreCommitConfig;
use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitIssue;

/**
 * Check Naming Conventions
 *
 * Enforces naming conventions:
 * - Folders say flow or capability
 * - Units say responsibility
 * - Functions say exact action
 * - No generic names (Services, Helpers, Utils, etc.)
 */
final class CheckNamingConventions
{
    private PreCommitConfig $config;

    /** @var array<string> */
    private array $forbiddenNames
        = [
            'Services', 'Helpers', 'Utils', 'Common', 'Shared',
            'Managers', 'Core', 'Support', 'Misc', 'Stuff',
            'Base', 'Generic', 'Manager', 'Helper', 'Util',
        ];

    public function __construct(PreCommitConfig $config)
    {
        $this->config = $config;
    }

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

            // Check for forbidden names in path
            $dirPath  = dirname($filePath);
            $fileName = basename($filePath);

            foreach ($this->forbiddenNames as $forbidden) {
                if (stripos($dirPath, '/' . $forbidden . '/') !== false ||
                    stripos($fileName, $forbidden) !== false) {
                    $issues[] = new PreCommitIssue(
                        'CheckNamingConventions',
                        PreCommitIssue::SEVERITY_ERROR,
                        "Forbidden name '{$forbidden}' in path",
                        $file,
                        null,
                        'NAMING_FORBIDDEN'
                    );
                }
            }

            // Check PHP class naming
            if (str_ends_with(strtolower($file), '.php')) {
                $classIssues = $this->checkPhpClassNaming($filePath, $file);
                $issues      = array_merge($issues, $classIssues);
            }
        }

        return $issues;
    }

    /**
     * Check PHP class naming conventions
     *
     * @return array<PreCommitIssue>
     */
    private function checkPhpClassNaming(string $filePath, string $relativePath) : array
    {
        $issues = [];

        $content = file_get_contents($filePath);
        if ($content === false) {
            return $issues;
        }

        // Extract class names
        $tokens    = token_get_all($content);
        $classes   = [];
        $namespace = '';

        foreach ($tokens as $token) {
            if (! is_array($token)) {
                continue;
            }

            if ($token[0] === T_NAMESPACE) {
                $i        = 1;
                $nsTokens = [];
                while ( isset($tokens[$i]) && is_array($tokens[$i]) &&
                    in_array($tokens[$i][0], [T_STRING, T_NS_SEPARATOR]) ) {
                    $nsTokens[] = $tokens[$i][1];
                    $i++;
                }
                $namespace = implode('', $nsTokens);
                continue;
            }

            if ($token[0] === T_CLASS && isset($tokens[$token[1]])) {
                $i = $token[1] + 1;
                while ( isset($tokens[$i]) && is_array($tokens[$i]) &&
                    $tokens[$i][0] === T_WHITESPACE ) {
                    $i++;
                }
                if (isset($tokens[$i]) && is_array($tokens[$i]) &&
                    $tokens[$i][0] === T_STRING) {
                    $classes[] = $tokens[$i][1];
                }
            }
        }

        foreach ($classes as $className) {
            // Check for PascalCase
            if (! preg_match('/^[A-Z][a-zA-Z0-9]*$/', $className)) {
                $issues[] = new PreCommitIssue(
                    'CheckNamingConventions',
                    PreCommitIssue::SEVERITY_ERROR,
                    "Class '{$className}' must use PascalCase",
                    $relativePath,
                    null,
                    'NAMING_PASCAL'
                );
            }

            // Check for forbidden names
            foreach ($this->forbiddenNames as $forbidden) {
                if (stripos($className, $forbidden) !== false) {
                    $issues[] = new PreCommitIssue(
                        'CheckNamingConventions',
                        PreCommitIssue::SEVERITY_ERROR,
                        "Class '{$className}' contains forbidden name '{$forbidden}'",
                        $relativePath,
                        null,
                        'NAMING_FORBIDDEN_CLASS'
                    );
                }
            }
        }

        return $issues;
    }
}
