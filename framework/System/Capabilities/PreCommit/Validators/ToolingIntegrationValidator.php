<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Validators;

use Avax\Framework\System\Capabilities\PreCommit\ValidationResult;

/**
 * Tooling Integration Validator
 *
 * Ensures tooling scripts and integration tests follow conventions.
 */
class ToolingIntegrationValidator extends BaseValidator
{
    public function getName(): string
    {
        return 'ToolingIntegrationValidator';
    }

    public function validate(array $context): ValidationResult
    {
        $files = $context['staged_files'] ?? [];
        $basePath = $context['base_path'] ?? getcwd();
        $messages = [];

        foreach ($files as $file) {
            // Check tooling directory files follow .sh extension
            if (str_starts_with((string) $file, 'tooling/') && preg_match('/\.(php|sh)$/', (string) $file)) {
                $filePath = $basePath.'/'.$file;
                if (! file_exists($filePath)) {
                    continue;
                }

                // Shell scripts should have proper shebang
                if (str_ends_with((string) $file, '.sh')) {
                    $content = file_get_contents($filePath);
                    if ($content !== false && ! str_starts_with(trim($content), '#!/')) {
                        $messages[] = sprintf(
                            'Shell script %s missing shebang',
                            $file
                        );
                    }
                }
            }
        }

        return new ValidationResult(
            $messages === [],
            $messages,
            $messages === [] ? 'info' : 'warning',
            null,
            null,
            'TOOLING_INTEGRATION_013'
        );
    }
}
