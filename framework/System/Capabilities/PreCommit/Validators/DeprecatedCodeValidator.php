<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Validators;

use Avax\Framework\System\Capabilities\PreCommit\ValidationResult;

/**
 * Deprecated Code Validator
 *
 * Detects deprecated functions and patterns.
 */
class DeprecatedCodeValidator extends BaseValidator
{
    public function getName() : string
    {
        return 'DeprecatedCodeValidator';
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

            // Check for deprecated patterns
            $deprecated = [
                '/\$this->validate\(/',
                '/\$this->validateAll\(/',
            ];

            foreach ($deprecated as $pattern) {
                if (preg_match($pattern, $content)) {
                    $messages[] = sprintf(
                        "Deprecated pattern detected in %s",
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
            'DEPRECATED_CODE_011'
        );
    }
}
