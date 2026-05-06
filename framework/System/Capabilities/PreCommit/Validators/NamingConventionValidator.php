<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Validators;

use Avax\Framework\System\Capabilities\PreCommit\ValidationResult;
use Override;

/**
 * Naming Convention Validator
 *
 * Enforces strict naming conventions per architectural standards:
 * - Folders say flow or capability
 * - Units say responsibility
 * - Functions say exact action
 * - No generic names (Services, Helpers, Utils, etc.)
 */
class NamingConventionValidator extends BaseValidator
{
    /** @var array<string> */
    private array $forbiddenNames = [
        'Services', 'Helpers', 'Utils', 'Common', 'Shared',
        'Managers', 'Core', 'Support', 'Misc', 'Stuff',
        'Base', 'Generic', 'Manager', 'Helper', 'Util',
        'ServiceManager', 'CommonUtils', 'SharedService',
        'CoreStuff', 'DataHelpers', 'BaseHandler', 'MiscFunctions',
        'GenericProcessor', 'SharedThings', 'InternalHelpers',
    ];

    public function __construct()
    {
        parent::__construct('NamingConventionValidator');
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
            $filePath = $basePath.'/'.$file;
            if (! file_exists($filePath)) {
                continue;
            }

            // Check directory/file names for forbidden patterns
            $dirPath = dirname($filePath);
            $fileName = basename($filePath);

            // Check for forbidden names in path
            foreach ($this->forbiddenNames as $forbiddenName) {
                if (stripos($dirPath, '/'.$forbiddenName.'/') !== false ||
                    stripos($dirPath, '/src/'.$forbiddenName) !== false ||
                    stripos($fileName, $forbiddenName) !== false) {
                    $allPassed = false;
                    $messages[] = sprintf(
                        "Forbidden name '%s' detected in path: %s",
                        $forbiddenName,
                        $file
                    );
                }
            }

            // Check PHP class naming (if PHP file)
            if (str_ends_with(strtolower((string) $file), '.php')) {
                $phpResult = $this->checkPhpClassNaming($filePath, $file);
                if (! $phpResult->isPassed()) {
                    $allPassed = false;
                    $messages = array_merge($messages, $phpResult->getMessages());
                }
            }
        }

        $validationResult = new ValidationResult(
            $allPassed,
            $messages,
            $allPassed ? 'info' : 'error',
            null,
            null,
            'NAMING_CONVENTION_001'
        );

        return $this->combineWithNext($context, $validationResult);
    }

    /**
     * Check PHP class naming conventions
     */
    private function checkPhpClassNaming(string $filePath, string $relativePath): ValidationResult
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return ValidationResult::pass();
        }

        // Extract class names
        $tokens = token_get_all($content);
        $classes = [];
        $namespace = '';
        $i = 0;

        while ($i < count($tokens)) {
            $token = $tokens[$i];

            if (is_array($token)) {
                if ($token[0] === T_NAMESPACE) {
                    $i += 2; // Skip namespace keyword and whitespace
                    $nsTokens = [];
                    while (isset($tokens[$i]) && is_array($tokens[$i]) &&
                        in_array($tokens[$i][0], [T_STRING, T_NS_SEPARATOR], true)) {
                        $nsTokens[] = $tokens[$i][1];
                        $i++;
                    }

                    $namespace = implode('', $nsTokens);
                }

                if ($token[0] === T_CLASS) {
                    // Find the class name
                    $j = $i + 1;
                    while ($j < count($tokens) && is_array($tokens[$j]) &&
                           $tokens[$j][0] === T_WHITESPACE) {
                        $j++;
                    }

                    if (isset($tokens[$j]) && is_array($tokens[$j]) &&
                        $tokens[$j][0] === T_STRING) {
                        $classes[] = $tokens[$j][1];
                    }
                }
            }

            $i++;
        }

        $messages = [];
        $allPassed = true;

        foreach ($classes as $class) {
            // Check for PascalCase
            if (! preg_match('/^[A-Z][a-zA-Z0-9]*$/', $class)) {
                $allPassed = false;
                $messages[] = sprintf(
                    "Class '%s' in %s must use PascalCase",
                    $class,
                    $relativePath
                );
            }

            // Check for forbidden names
            foreach ($this->forbiddenNames as $forbiddenName) {
                if (stripos($class, $forbiddenName) !== false) {
                    $allPassed = false;
                    $messages[] = sprintf(
                        "Class '%s' contains forbidden name '%s' in %s",
                        $class,
                        $forbiddenName,
                        $relativePath
                    );
                }
            }

            // Check for descriptive naming
            if (strlen($class) < 3 && ! in_array($class, ['Id', 'Ip', 'Io', 'Db'], true)) {
                $allPassed = false;
                $messages[] = sprintf(
                    "Class name '%s' in %s is too short to be descriptive",
                    $class,
                    $relativePath
                );
            }
        }

        return new ValidationResult(
            $allPassed,
            $messages,
            $allPassed ? 'info' : 'error',
            $relativePath,
            null,
            'NAMING_CONVENTION_CLASS_002'
        );
    }

    #[Override]
    public function supports(array $context): bool
    {
        return ! empty($context['staged_files'] ?? []);
    }
}
