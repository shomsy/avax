<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Validators;

use Avax\Framework\System\Capabilities\PreCommit\ValidationResult;

/**
 * Todo Comment Validator
 *
 * Detects TODO, FIXME, NOTE comments in code.
 */
class TodoCommentValidator extends BaseValidator
{
    public function getName() : string
    {
        return 'TodoCommentValidator';
    }

    public function validate(array $context) : ValidationResult
    {
        $files    = $context['staged_files'] ?? [];
        $basePath = $context['base_path'] ?? getcwd();
        $messages = [];

        foreach ($files as $file) {
            if (! str_ends_with(strtolower((string) $file), '.php')) {
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

            // Check for TODO/FIXME/NOTE/HACK comments (excluding our own .agents dir)
            // Don't flag if in .agents directory or test files
            if ((stripos($content, 'TODO') !== false || stripos($content, 'FIXME') !== false || stripos($content, 'NOTE:') !== false || stripos($content, 'HACK') !== false) && (! str_contains((string) $file, '.agents') && ! str_contains((string) $file, '/tests/'))) {
                $messages[] = sprintf(
                    "TODO/FIXME/NOTE/HACK comment found in %s",
                    $file
                );
            }
        }

        return new ValidationResult(
            $messages === [],
            $messages,
            $messages === [] ? 'info' : 'warning',
            null,
            null,
            'TODO_COMMENT_012'
        );
    }
}
