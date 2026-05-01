<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Validators;

use Avax\Framework\System\Capabilities\PreCommit\ValidationResult;

/**
 * Legacy Code Validator
 *
 * Detects legacy code patterns that should be migrated:
 * - Legacy folders (DataFoundation, components/Legacy, etc.)
 * - Legacy aliases in compat.php
 * - Files that need cleanup
 * - Legacy shriomove and temporary folders
 * - Files marked for deletion
 */
class LegacyCodeValidator extends BaseValidator
{
    /** @var array<string> */
    private array $legacyFolders
        = [
            'DataFoundation',
            'components/Legacy',
            'legacy',
            'Deprecated',
            'Old',
            'Temp',
            'tmp',
            'shriomove',
            '.shriomove',
        ];

    /** @var array<string> */
    private array $legacyPatterns
        = [
            '# Legacy code' => 'contains legacy marker',
            '@legacy'       => 'has @legacy annotation',
            'class_alias('  => 'uses class_alias (legacy)',
        ];

    /** @var array<string> */
    private array $compatFiles
        = [
            'components/compat.php',
            'app/Compat.php',
            'bootstrap/compat.php',
        ];

    /** @var array<string> */
    private array $cleanupSuggestions
        = [
            'legacy_files_to_delete' => 'files marked for deletion',
            'shriomove'              => 'temporary move directory',
            '.shriomove'             => 'hidden temporary move directory',
        ];

    private ?string $compatPath;

    public function __construct(?string $basePath = null)
    {
        parent::__construct('LegacyCodeValidator');
        $this->compatPath = $basePath ? $basePath . '/components/compat.php' : null;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function validate(array $context) : ValidationResult
    {
        $files     = $context['staged_files'] ?? [];
        $basePath = $context['base_path'] ?? getcwd();
        $messages = [];
        $allPassed = true;

        // Check for legacy folders in staged files
        foreach ($files as $file) {
            $filePath = $basePath . '/' . $file;
            if (! file_exists($filePath)) {
                continue;
            }

            // Check for legacy folder patterns in path
            $legacyResult = $this->checkLegacyFolders($filePath, $file);
            if (! $legacyResult->isPassed()) {
                $allPassed = false;
                $messages  = array_merge($messages, $legacyResult->getMessages());
            }

            // Check content for legacy patterns
            if (preg_match('/\.(php|js|ts|py|go|sh)$/', $file)) {
                $content = file_get_contents($filePath);
                if ($content !== false) {
                    $contentResult = $this->checkLegacyContent($filePath, $file, $content);
                    if (! $contentResult->isPassed()) {
                        $allPassed = false;
                        $messages  = array_merge($messages, $contentResult->getMessages());
                    }
                }
            }

            // Check for deprecated alias usage in PHP files
            if (str_ends_with(strtolower($filePath), '.php')) {
                $phpResult = $this->checkLegacyAliases($filePath, $file);
                if (! $phpResult->isPassed()) {
                    $allPassed = false;
                    $messages  = array_merge($messages, $phpResult->getMessages());
                }
            }
        }

        // Check for legacy aliases in compat.php
        $compatResult = $this->checkCompatAliases($basePath);
        if (! $compatResult->isPassed()) {
            $allPassed = false;
            $messages  = array_merge($messages, $compatResult->getMessages());
        }

        // Check for legacy folders that need cleanup
        $cleanupResult = $this->checkLegacyFoldersForCleanup($basePath);
        if (! $cleanupResult->isPassed()) {
            $allPassed = false;
            $messages  = array_merge($messages, $cleanupResult->getMessages());
        }

        $result = new ValidationResult(
            $allPassed,
            $messages,
            $allPassed ? 'info' : 'warning',
            null,
            null,
            'LEGACY_CODE_001'
        );

        return $this->combineWithNext($context, $result);
    }

    /**
     * Check for legacy folder patterns
     */
    private function checkLegacyFolders(string $filePath, string $relativePath) : ValidationResult
    {
        $messages  = [];
        $allPassed = true;

        foreach ($this->legacyFolders as $folder) {
            if (str_contains($filePath, '/' . $folder . '/') ||
                str_contains($filePath, '/' . $folder . '\\')) {
                $allPassed  = false;
                $messages[] = sprintf(
                    "File '%s' is in legacy folder '%s'",
                    $relativePath,
                    $folder
                );
            }
        }

        return new ValidationResult(
            $allPassed,
            $messages,
            $allPassed ? 'info' : 'warning',
            $relativePath,
            null,
            'LEGACY_FOLDER_001'
        );
    }

    /**
     * Check content for legacy patterns
     */
    private function checkLegacyContent(string $filePath, string $relativePath, string $content) : ValidationResult
    {
        $messages  = [];
        $allPassed = true;

        foreach ($this->legacyPatterns as $pattern => $description) {
            if (stripos($content, $pattern) !== false) {
                // Skip if in comment
                if ($this->isInComment($content, $pattern)) {
                    continue;
                }
                $allPassed  = false;
                $messages[] = sprintf(
                    "File '%s' %s: '%s'",
                    $relativePath,
                    $description,
                    $pattern
                );
            }
        }

        return new ValidationResult(
            $allPassed,
            $messages,
            $allPassed ? 'info' : 'warning',
            $relativePath,
            null,
            'LEGACY_CONTENT_002'
        );
    }

    /**
     * Check for legacy alias usage
     */
    private function checkLegacyAliases(string $filePath, string $relativePath) : ValidationResult
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return ValidationResult::pass();
        }

        $messages  = [];
        $allPassed = true;

        // Check for class_alias to legacy classes
        if (preg_match_all('/class_alias\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,/', $content, $matches)) {
            foreach ($matches[1] as $alias) {
                $allPassed  = false;
                $messages[] = sprintf(
                    "File '%s' uses class_alias to '%s' (legacy pattern)",
                    $relativePath,
                    $alias
                );
            }
        }

        // Check for deprecated require/include patterns
        if (preg_match_all('/require(?:_once)?\s*\(\s*[\'"].*(?:legacy|compat|deprecated).*[\'"]/', $content, $matches)) {
            $allPassed = false;
            foreach ($matches[0] as $match) {
                $messages[] = sprintf(
                    "File '%s' uses legacy require: %s",
                    $relativePath,
                    $match
                );
            }
        }

        return new ValidationResult(
            $allPassed,
            $messages,
            $allPassed ? 'info' : 'warning',
            $relativePath,
            null,
            'LEGACY_ALIAS_002'
        );
    }

    /**
     * Check compat.php for legacy aliases
     */
    private function checkCompatAliases(string $basePath) : ValidationResult
    {
        $messages  = [];
        $allPassed = true;

        // Check for compat.php in various locations
        foreach ($this->compatFiles as $compatFile) {
            $compatPath = $basePath . '/' . $compatFile;
            if (! file_exists($compatPath)) {
                continue;
            }

            $content = file_get_contents($compatPath);
            if ($content === false) {
                continue;
            }

            // Check for class_alias entries
            if (preg_match_all('/class_alias\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,/', $content, $matches)) {
                $allPassed = false;
                foreach ($matches[1] as $alias) {
                    $messages[] = sprintf(
                        "compat.php has class_alias to '%s' (should remove legacy alias)",
                        $alias
                    );
                }
            }

            // Check for deprecated function wrappers
            if (preg_match_all('/function\s+(\w+)\s*\(\([^)]*\))\s*\{[^}]*@deprecated/ims', $content, $matches)) {
                $allPassed  = false;
                $messages[] = sprintf(
                    "compat.php has deprecated function wrappers: %s",
                    implode(', ', $matches[1])
                );
            }
        }

        return new ValidationResult(
            $allPassed,
            $messages,
            $allPassed ? 'info' : 'warning',
            null,
            null,
            'LEGACY_COMPAT_003'
        );
    }

    /**
     * Check for legacy folders that should be cleaned up
     */
    private function checkLegacyFoldersForCleanup(string $basePath) : ValidationResult
    {
        $messages  = [];
        $allPassed = true;

        // Check for shriomove and other temporary folders
        $foldersToCheck = ['shriomove', '.shriomove', 'tmp', 'Temp', 'temp'];

        foreach ($foldersToCheck as $folder) {
            $folderPath = $basePath . '/' . $folder;
            if (is_dir($folderPath)) {
                $allPassed = false;
                // Count files in folder
                $fileCount  = count(glob($folderPath . '/*'));
                $messages[] = sprintf(
                    "Legacy folder '%s' exists with %d files - consider cleaning up",
                    $folder,
                    $fileCount
                );
            }

            // Check for DataFoundation
            $dataFoundationPath = $basePath . '/DataFoundation';
            if (is_dir($dataFoundationPath)) {
                $allPassed  = false;
                $messages[] = "Legacy 'DataFoundation' folder exists - should be migrated to components/";
            }

            // Check for components/Legacy
            $componentsLegacyPath = $basePath . '/components/Legacy';
            if (is_dir($componentsLegacyPath)) {
                $allPassed  = false;
                $messages[] = "Legacy 'components/Legacy' folder exists - should be cleaned up";
            }
        }

        return new ValidationResult(
            $allPassed,
            $messages,
            $allPassed ? 'info' : 'warning',
            null,
            null,
            'LEGACY_CLEANUP_004'
        );
    }

    /**
     * Check if pattern is inside a comment
     */
    private function isInComment(string $content, string $pattern) : bool
    {
        $pos = stripos($content, $pattern);
        if ($pos === false) {
            return false;
        }

        $before           = substr($content, 0, $pos);
        $lastBlockComment = strrpos($before, '/*');
        $lastLineComment  = strrpos($before, '//');
        $lastHashComment  = strrpos($before, '#');

        // Check if inside block comment
        if ($lastBlockComment !== false) {
            $blockEnd = strpos($content, '*/', $lastBlockComment);
            if ($blockEnd !== false && $blockEnd > $pos) {
                return true;
            }
        }

        // Check if inside line comment
        $lastNewline = strrpos($before, "\n");
        if ($lastLineComment !== false && ($lastNewline === false || $lastLineComment > $lastNewline)) {
            return true;
        }
        if ($lastHashComment !== false && ($lastNewline === false || $lastHashComment > $lastNewline)) {
            return true;
        }

        return false;
    }

    /**
     * Add custom legacy folder to check
     */
    public function addLegacyFolder(string $folder) : self
    {
        if (! in_array($folder, $this->legacyFolders)) {
            $this->legacyFolders[] = $folder;
        }

        return $this;
    }

    public function supports(array $context) : bool
    {
        return ! empty($context['staged_files'] ?? []);
    }
}
