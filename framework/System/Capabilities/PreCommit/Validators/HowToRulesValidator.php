<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Validators;

use Avax\Framework\System\Capabilities\PreCommit\ValidationResult;
use Override;

/**
 * How-To Rules Validator
 * 
 * Reads .agents/how-to/*.md files and validates that
 * code changes comply with documented rules.
 */
class HowToRulesValidator extends BaseValidator
{
    private readonly string $howToDir;

    /** @var array<string, array{file: string, forbidden_patterns: list<string>, required_patterns: list<string>}> */
    private array $rules = [];

    public function __construct(?string $howToDir = null)
    {
        parent::__construct('HowToRulesValidator');
        $this->howToDir = $howToDir ?? ((getcwd() ?: '.') . '/.agents/how-to');
        $this->loadRules();
    }

    public function getName(): string
    {
        return $this->name;
    }

    private function loadRules(): void
    {
        if (!is_dir($this->howToDir)) {
            return;
        }

        $files = glob($this->howToDir . '/how-to-*.md') ?: [];
        foreach ($files as $file) {
            $this->parseRuleFile($file);
        }
    }

    private function parseRuleFile(string $filePath): void
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return;
        }

        $fileName = basename($filePath, '.md');

        // Extract forbidden patterns from how-to files
        $forbiddenPatterns = [];
        $requiredPatterns = [];

        // Look for "Forbidden" or "Avoid" sections
        if (preg_match_all('/##\s+(?:Forbidden|Avoid|Must Not|Do Not)(?:\s+Patterns)?\s*\n(.*?)(?=\n##|\n#|\Z)/is', $content, $matches)) {
            foreach ($matches[1] as $section) {
                // Extract words in backticks or code blocks
                preg_match_all('/`([^`]+)`/', $section, $codeMatches);
                foreach ($codeMatches[1] as $code) {
                    if (! in_array(trim($code), ['', '0'], true)) {
                        $forbiddenPatterns[] = $code;
                    }
                }
            }
        }

        // Look for naming restrictions
        if (preg_match_all('/forbidden (?:name|names|pattern)?s?[\s:]+([A-Z][a-zA-Z,\s]+)/i', $content, $matches)) {
            foreach ($matches[1] as $match) {
                $names = preg_split('/[,\s]+/', $match) ?: [];
                foreach ($names as $name) {
                    $name = trim($name);
                    if ($name !== '' && $name !== '0') {
                        $forbiddenPatterns[] = $name;
                    }
                }
            }
        }

        $this->rules[$fileName] = [
            'file' => $fileName,
            'forbidden_patterns' => array_values(array_unique($forbiddenPatterns)),
            'required_patterns' => $requiredPatterns,
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function validate(array $context): ValidationResult
    {
        if ($this->rules === []) {
            return $this->passToNext($context);
        }

        $files    = is_array($context['staged_files'] ?? null) ? $context['staged_files'] : [];
        $basePath = is_string($context['base_path'] ?? null) ? $context['base_path'] : (getcwd() ?: '.');
        $messages = [];
        $allPassed = true;

        foreach ($files as $file) {
            if (! is_string($file)) {
                continue;
            }

            $filePath = $basePath . '/' . $file;
            if (!file_exists($filePath)) {
                continue;
            }

            // Only check source files
            if (!preg_match('/\.(php|js|ts|py|go)$/', $file)) {
                continue;
            }

            $content = file_get_contents($filePath);
            if ($content === false) {
                continue;
            }

            // Check against forbidden patterns from how-to rules
            foreach ($this->rules as $ruleName => $rule) {
                foreach ($rule['forbidden_patterns'] as $pattern) {
                    // Simple string match for forbidden patterns
                    if (stripos($content, $pattern) !== false) {
                        // Skip if it's in a comment
                        if ($this->isInComment($content, $pattern)) {
                            continue;
                        }

                        $allPassed = false;
                        $messages[] = sprintf(
                            "Pattern '%s' from %s violates documented rule in %s",
                            $pattern,
                            $ruleName,
                            $file
                        );
                    }
                }
            }

            // PHP-specific checks from how-to-coding-standards.md
            if (str_ends_with(strtolower($file), '.php')) {
                $phpResult = $this->checkPhpStandards($filePath, $file, $content);
                if (!$phpResult->isPassed()) {
                    $allPassed = false;
                    $messages = array_merge($messages, $phpResult->getMessages());
                }
            }
        }

        $validationResult = new ValidationResult(
            $allPassed,
            $messages,
            $allPassed ? 'info' : 'warning',
            null,
            null,
            'HOWTO_RULES_003'
        );

        return $this->combineWithNext($context, $validationResult);
    }

    /**
     * Check PHP-specific coding standards from how-to documents
     */
    private function checkPhpStandards(string $filePath, string $relativePath, string $content): ValidationResult
    {
        $messages = [];
        $allPassed = true;

        $tokens = token_get_all($content);

        foreach ($tokens as $token) {
            if (!is_array($token)) {
                continue;
            }

            // Check for deprecated patterns
            // Check for use of var (should use modern type declarations)
            if ($token[0] === T_VAR) {
                $allPassed = false;
                $messages[] = sprintf(
                    "Use of 'var' keyword is not modern PHP 8.5+ in %s",
                    $relativePath
                );
            }
        }

        // Check for missing declare(strict_types=1)
        if (str_ends_with(strtolower($filePath), '.php')) {
            $fileLines  = file($filePath) ?: [];
            $firstLines = implode('\n', array_slice($fileLines, 0, 10));
            // Check if it's a class file (has class keyword)
            if (! str_contains($firstLines, 'declare(strict_types=1)') && ! str_contains($firstLines, 'declare(strict_types=1);') && (str_contains($content, 'class ') || str_contains($content, 'interface '))) {
                $allPassed  = false;
                $messages[] = sprintf(
                    "Missing 'declare(strict_types=1)' in PHP file %s",
                    $relativePath
                );
            }
        }

        return new ValidationResult(
            $allPassed,
            $messages,
            $allPassed ? 'info' : 'warning',
            $relativePath,
            null,
            'PHP_STANDARDS_004'
        );
    }

    /**
     * Check if a pattern match is inside a comment
     */
    private function isInComment(string $content, string $pattern): bool
    {
        $pos = stripos($content, $pattern);
        if ($pos === false) {
            return false;
        }

        $before = substr($content, 0, $pos);
        $lastBlockComment = strrpos($before, '/*');
        $lastLineComment = strrpos($before, '//');
        $lastHashComment = strrpos($before, '#');

        // Check if inside block comment
        if ($lastBlockComment !== false) {
            $blockEnd = strpos($content, '*/', $lastBlockComment);
            if ($blockEnd !== false && $blockEnd > $pos) {
                return true;
            }
        }

        // Check if inside line comment (after last newline)
        $lastNewline = strrpos($before, "\n");
        if ($lastLineComment !== false && ($lastNewline === false || $lastLineComment > $lastNewline)) {
            return true;
        }

        return $lastHashComment !== false && ($lastNewline === false || $lastHashComment > $lastNewline);
    }

    /**
     * @param array<string, mixed> $context
     */
    #[Override]
    public function supports(array $context): bool
    {
        return !empty($context['staged_files'] ?? []);
    }
}
