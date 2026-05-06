<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Validators;

use Avax\Framework\System\Capabilities\PreCommit\ValidationResult;
use Override;

/**
 * PHP Syntax Validator
 *
 * Validates PHP syntax using lint and static analysis.
 */
class PhpSyntaxValidator extends BaseValidator
{
    public function __construct()
    {
        parent::__construct('PhpSyntaxValidator');
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
            if (! str_ends_with(strtolower((string) $file), '.php')) {
                continue;
            }

            $filePath = $basePath.'/'.$file;
            if (! file_exists($filePath)) {
                continue;
            }

            // PHP lint check
            $lintResult = $this->lintPhpFile($filePath);
            if (! $lintResult->isPassed()) {
                $allPassed = false;
                $messages = array_merge($messages, $lintResult->getMessages());
            }

            // Check for modern PHP standards
            $modernResult = $this->checkModernPhp($filePath, $file);
            if (! $modernResult->isPassed()) {
                // Don't fail on modern PHP (warning only)
                $messages = array_merge($messages, $modernResult->getMessages());
            }
        }

        $severity = $allPassed ? 'info' : 'error';
        $validationResult = new ValidationResult(
            $allPassed,
            $messages,
            $severity,
            null,
            null,
            'PHP_SYNTAX_005'
        );

        return $this->combineWithNext($context, $validationResult);
    }

    /**
     * Lint PHP file for syntax errors
     */
    private function lintPhpFile(string $filePath): ValidationResult
    {
        $output = [];
        $returnVar = 0;
        exec('php -l '.escapeshellarg($filePath).' 2>&1', $output, $returnVar);

        if ($returnVar !== 0) {
            return ValidationResult::fail(
                'PHP Syntax Error: '.implode(' ', $output),
                'error',
                $filePath,
                null,
                'PHP_LINT_ERROR'
            );
        }

        return ValidationResult::pass();
    }

    /**
     * Check for modern PHP 8.x features usage
     */
    private function checkModernPhp(string $filePath, string $relativePath): ValidationResult
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return ValidationResult::pass();
        }

        $messages = [];
        $tokens = token_get_all($content);

        $hasConstructorPromotion = false;
        $hasReadonlyProps = false;
        $hasEnums = false;
        $hasAttributes = false;
        $usesModernSyntax = false;
        $counter = count($tokens);

        for ($i = 0; $i < $counter; $i++) {
            $token = $tokens[$i];

            if (! is_array($token)) {
                // Check for constructor promotion syntax
                if ($token === '#' && isset($tokens[$i + 1]) && is_array($tokens[$i + 1]) &&
                    $tokens[$i + 1][0] === T_PRIVATE) {
                    $hasConstructorPromotion = true;
                    $usesModernSyntax = true;
                }

                continue;
            }

            // Check for readonly keyword
            if ($token[0] === T_READONLY) {
                $hasReadonlyProps = true;
                $usesModernSyntax = true;
            }

            // Check for enum keyword
            if ($token[0] === T_ENUM) {
                $hasEnums = true;
                $usesModernSyntax = true;
            }

            // Check for attributes
            if ($token[0] === T_ATTRIBUTE) {
                $hasAttributes = true;
                $usesModernSyntax = true;
            }
        }

        // Only warn if no modern syntax at all (educational notice)
        if (! $usesModernSyntax) {
            $messages[] = sprintf(
                'Consider using modern PHP 8.x features (readonly, enums, attributes, constructor promotion) in %s',
                $relativePath
            );
        }

        return new ValidationResult(
            true, // Always pass (just a warning/notice)
            $messages,
            'info',
            $relativePath,
            null,
            'PHP_MODERN_SYNTAX_NOTICE'
        );
    }

    #[Override]
    public function supports(array $context): bool
    {
        $files = $context['staged_files'] ?? [];
        foreach ($files as $file) {
            if (str_ends_with(strtolower((string) $file), '.php')) {
                return true;
            }
        }

        return false;
    }
}
