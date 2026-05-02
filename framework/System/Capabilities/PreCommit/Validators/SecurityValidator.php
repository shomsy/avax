<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Validators;

use Avax\Framework\System\Capabilities\PreCommit\ValidationResult;
use Override;

/**
 * Security Validator
 * 
 * Detects potential security issues in staged files.
 */
class SecurityValidator extends BaseValidator
{
    /** @var array<string> */
    private array $secretPatterns = [
        // API keys, tokens
        '/(api[_-]?key|apikey)\s*[=:]\s*["\']?[a-zA-Z0-9_\-]{20,}["\']?/i',
        '/(secret[_-]?key|secret)\s*[=:]\s*["\']?[a-zA-Z0-9_\-]{20,}["\']?/i',
        '/(access[_-]?token|access_token)\s*[=:]\s*["\']?[a-zA-Z0-9_\-\.]{20,}["\']?/i',
        '/(auth[_-]?token|auth_token)\s*[=:]\s*["\']?[a-zA-Z0-9_\-\.]{20,}["\']?/i',
        
        // Passwords in code
        '/(password|passwd)\s*[=:]\s*["\'][^"\'\s]{3,}["\']/i',
        
        // AWS keys
        '/(aws[_-]?secret|aws[_-]?key)\s*[=:]\s*["\']?[A-Z0-9\/+=]{20,}["\']?/i',
        
        // Private keys (simplified)
        '/-----BEGIN (RSA |DSA |EC |OPENSSH |PGP )?PRIVATE KEY-----/i',
        
        // Database connection strings with credentials
        '/(mysql|pgsql|sqlite|mongodb):\/\/[^\s]+:[^\s]+@[^\s]+/i',
    ];

    public function __construct()
    {
        parent::__construct('SecurityValidator');
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function validate(array $context): ValidationResult
    {
        $files = $context['staged_files'] ?? [];
        $basePath = $context['base_path'] ?? getcwd();
        $messages = [];
        $allPassed = true;

        foreach ($files as $file) {
            $filePath = $basePath . '/' . $file;
            if (!file_exists($filePath)) {
                continue;
            }

            // Skip binary files
            if (!$this->isTextFile($filePath)) {
                continue;
            }

            $content = file_get_contents($filePath);
            if ($content === false) {
                continue;
            }

            // Check for secrets
            $secretResult = $this->checkForSecrets($content, $file);
            if (!$secretResult->isPassed()) {
                $allPassed = false;
                $messages = array_merge($messages, $secretResult->getMessages());
            }

            // Check for dangerous functions (only in PHP files)
            if (str_ends_with(strtolower((string) $file), '.php')) {
                $securityResult = $this->checkPhpSecurity($content, $file);
                if (!$securityResult->isPassed()) {
                    $allPassed = false;
                    $messages = array_merge($messages, $securityResult->getMessages());
                }
            }
        }

        $validationResult = new ValidationResult(
            $allPassed,
            $messages,
            $allPassed ? 'info' : 'critical',
            null,
            null,
            'SECURITY_CHECK_009'
        );

        return $this->combineWithNext($context, $validationResult);
    }

    /**
     * Check for hardcoded secrets
     */
    private function checkForSecrets(string $content, string $file): ValidationResult
    {
        $messages = [];

        foreach ($this->secretPatterns as $secretPattern) {
            if (preg_match_all($secretPattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    $lineNum = substr_count(substr($content, 0, $match[1]), "\n") + 1;
                    $messages[] = sprintf(
                        "Potential secret/hardcoded credential detected in %s at line %d: %s",
                        $file,
                        $lineNum,
                        substr($match[0], 0, 50)
                    );
                }
            }
        }

        if ($messages !== []) {
            return ValidationResult::fail(
                'Security check failed - potential secrets detected',
                'critical',
                $file,
                null,
                'SECRET_DETECTED'
            );
        }

        return ValidationResult::pass();
    }

    /**
     * Check PHP file for dangerous functions
     */
    private function checkPhpSecurity(string $content, string $file): ValidationResult
    {
        $messages = [];
        $tokens = token_get_all($content);

        $foundDangerous = [];
        foreach ($tokens as $token) {
            if (!is_array($token)) {
                continue;
            }

            if ($token[0] === T_STRING) {
                $funcName = strtolower($token[1]);
                // Check for truly dangerous functions, excluding allowed ones
                $dangerous = ['eval', 'assert', 'create_function'];
                // Check if it's in a comment
                if (in_array($funcName, $dangerous, true) && ! $this->isTokenInComment($tokens, $token)) {
                    $foundDangerous[$token[1]] = true;
                }
            }
        }

        foreach (array_keys($foundDangerous) as $func) {
            $messages[] = sprintf(
                "Dangerous function '%s()' detected in %s",
                $func,
                $file
            );
        }

        if ($messages !== []) {
            return ValidationResult::fail(
                'Security check failed - dangerous functions detected',
                'error',
                $file,
                null,
                'DANGEROUS_FUNCTION'
            );
        }

        return ValidationResult::pass();
    }

    /**
     * Check if token is inside a comment
     *
     * @param list<array{int, string, int}|string> $tokens
     * @param array{int, string, int}              $targetToken
     */
    private function isTokenInComment(array $tokens, array $targetToken): bool
    {
        $inBlockComment = false;
        $inLineComment = false;

        foreach ($tokens as $token) {
            if ($token === $targetToken) {
                return $inBlockComment || $inLineComment;
            }

            if (is_array($token)) {
                if (($token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT) && $this->tokenContains($token[1], $targetToken[1])) {
                    return true;
                }

                if ($token[0] === T_WHITESPACE && str_contains($token[1], "\n")) {
                    $inLineComment = false;
                }
            } else {
                if ($token === '/*') {
                    $inBlockComment = true;
                }

                if ($token === '*/') {
                    $inBlockComment = false;
                }

                if ($token === '//') {
                    $inLineComment = true;
                }
            }
        }

        return false;
    }

    private function tokenContains(string $haystack, string $needle): bool
    {
        return str_contains($haystack, $needle);
    }

    /**
     * Simple text file detection
     */
    private function isTextFile(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $binaryExts = ['png', 'jpg', 'jpeg', 'gif', 'ico', 'pdf', 'zip', 'tar', 'gz', 'exe', 'bin'];

        return ! in_array($ext, $binaryExts, true);
    }

    #[Override]
    public function supports(array $context): bool
    {
        return !empty($context['staged_files'] ?? []);
    }
}
