<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Validators;

use Avax\Framework\System\Capabilities\PreCommit\ValidationResult;

/**
 * Legacy Code Validator
 *
 * Detects legacy patterns and deprecated code usage.
 */
class LegacyCodeValidator extends BaseValidator
{
    public function getName() : string
    {
        return 'LegacyCodeValidator';
    }

    public function validate(array $context) : ValidationResult
    {
        $files    = $context['staged_files'] ?? [];
        $basePath = $context['base_path'] ?? getcwd();
        $messages = [];

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

            // Check for legacy patterns
            $legacyPatterns = [
                '/\$this->(request|response)\s*->\w+\(\)/',
                '/global\s+\$/',
            ];

            foreach ($legacyPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    $messages[] = sprintf(
                        "Legacy pattern detected in %s",
                        $file
                    );
                }
            }
        }

        return new ValidationResult(
            empty($messages),
            $messages,
            empty($messages) ? 'info' : 'warning',
            null,
            null,
            'LEGACY_CODE_010'
        );
    }
}
