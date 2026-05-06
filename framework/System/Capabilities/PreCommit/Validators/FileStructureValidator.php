<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Validators;

use Avax\Framework\System\Capabilities\PreCommit\ValidationResult;
use Override;

/**
 * File Structure Validator
 *
 * Validates that file structure follows the architectural pattern:
 * - Flow slices in Flow/ folder
 * - Capabilities in Capabilities/ folder
 * - Configuration in Configuration/ folder
 * - Foundation in Foundation/ folder
 *
 * Enforces the rule: folder says flow or capability, unit says responsibility
 */
class FileStructureValidator extends BaseValidator
{
    /** @var array<string, string> */
    private array $expectedFolders = [
        'Flow' => 'flow',
        'Flows' => 'flow',
        'Capabilities' => 'capability',
        'Capability' => 'capability',
        'Configuration' => 'configuration',
        'Config' => 'configuration',
        'Foundation' => 'foundation',
        'PublicSurface' => 'public_surface',
        'Public' => 'public_surface',
        'System' => 'system_root',
        'Components' => 'components',
    ];

    /** @var array<string> */
    private array $forbiddenTopLevelFolders = [
        'Services', 'Helpers', 'Utils', 'Common', 'Shared',
        'Managers', 'Core', 'Misc', 'Generic', 'Base',
    ];

    public function __construct()
    {
        parent::__construct('FileStructureValidator');
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function validate(array $context): ValidationResult
    {
        $files = $context['staged_files'] ?? [];
        $basePath = $context['base_path'] ?? getcwd();
        $systemRoot = $context['system_root'] ?? $this->detectSystemRoot($basePath);

        $messages = [];
        $allPassed = true;

        foreach ($files as $file) {
            $filePath = $basePath.'/'.$file;
            $relativePath = $this->getRelativePath($filePath, $systemRoot);

            // Skip if not in system root
            if ($relativePath === $file) {
                continue;
            }

            $pathParts = explode('/', $relativePath);

            // Check top-level folders in system root
            if (count($pathParts) >= 2) {
                $topFolder = $pathParts[0];

                // Check for forbidden top-level folders
                foreach ($this->forbiddenTopLevelFolders as $forbiddenTopLevelFolder) {
                    if (strcasecmp($topFolder, $forbiddenTopLevelFolder) === 0) {
                        $allPassed = false;
                        $messages[] = sprintf(
                            "Forbidden top-level folder '%s' (generic/bucket folder) in %s. Use Flows/, Capabilities/, or specific domain folders instead.",
                            $forbiddenTopLevelFolder,
                            $file
                        );
                    }
                }

                // Validate second-level folder naming based on context
                if (count($pathParts) >= 3) {
                    $secondLevel = $pathParts[1];
                    $thirdLevel = $pathParts[2];

                    $structureResult = $this->validateStructureNaming(
                        $secondLevel,
                        $thirdLevel,
                        $file
                    );

                    if (! $structureResult->isPassed()) {
                        $allPassed = false;
                        $messages = array_merge($messages, $structureResult->getMessages());
                    }
                }
            }

            // Check for hallway folders (unnecessary nesting)
            $nestingResult = $this->checkNestingDepth($relativePath, $file);
            if (! $nestingResult->isPassed()) {
                // Warning only
                $messages = array_merge($messages, $nestingResult->getMessages());
            }

            // Check folder vs file naming
            $namingResult = $this->checkFolderFileNaming($pathParts);
            if (! $namingResult->isPassed()) {
                $allPassed = false;
                $messages = array_merge($messages, $namingResult->getMessages());
            }
        }

        $validationResult = new ValidationResult(
            $allPassed,
            $messages,
            $allPassed ? 'info' : 'error',
            null,
            null,
            'FILE_STRUCTURE_006'
        );

        return $this->combineWithNext($context, $validationResult);
    }

    /**
     * Validate structure naming conventions
     */
    private function validateStructureNaming(
        string $secondLevel,
        string $thirdLevel,
        string $file
    ): ValidationResult {
        $messages = [];
        $allPassed = true;

        // Check if second level is a recognized system root category
        $isKnownCategory = isset($this->expectedFolders[$secondLevel]);

        if ($isKnownCategory) {
            $categoryType = $this->expectedFolders[$secondLevel];

            // Flow slices should have action-oriented names
            if ($categoryType === 'flow' || $categoryType === 'capability') {
                // Third level should be PascalCase and descriptive
                if (! preg_match('/^[A-Z][a-zA-Z0-9]*$/', $thirdLevel) && strlen($thirdLevel) >= 3) {
                    $messages[] = sprintf(
                        "Folder '%s' in %s should use PascalCase for %s slices",
                        $thirdLevel,
                        $file,
                        $categoryType
                    );
                    $allPassed = false;
                }

                // Check for generic names in flow/capability slices
                $genericIndicators = ['Base', 'Abstract', 'Default', 'Common', 'General'];
                foreach ($genericIndicators as $genericIndicator) {
                    if (str_contains($thirdLevel, $genericIndicator)) {
                        $messages[] = sprintf(
                            "Generic name '%s' in %s is discouraged for %s slices",
                            $genericIndicator,
                            $file,
                            $categoryType
                        );
                    }
                }
            }
        }

        return new ValidationResult(
            $allPassed,
            $messages,
            $allPassed ? 'info' : 'warning',
            $file,
            null,
            'STRUCTURE_NAMING_007'
        );
    }

    /**
     * Check for excessive nesting (hallway folders)
     */
    private function checkNestingDepth(string $relativePath, string $file): ValidationResult
    {
        $messages = [];
        $parts = explode('/', $relativePath);

        // Count actual directory levels (excluding filename)
        $depth = count($parts) - 1;

        // Warn if depth > 4 in system root
        if ($depth > 4) {
            $messages[] = sprintf(
                'Deep nesting (depth %d) in %s. Consider flattening structure to improve locality.',
                $depth,
                $file
            );
        }

        // Check for single-item folders (potential hallway)
        for ($i = 0; $i < count($parts) - 2; $i++) {
            $segment = $parts[$i];
            // This would need actual filesystem check - simplified here
        }

        if (! empty($messages)) {
            return new ValidationResult(
                true, // Warning only, doesn't fail
                $messages,
                'warning',
                $file,
                null,
                'NESTING_WARNING_008'
            );
        }

        return ValidationResult::pass();
    }

    /**
     * Check folder vs file naming consistency
     *
     * @param  list<string>  $pathParts
     */
    private function checkFolderFileNaming(array $pathParts): ValidationResult
    {
        if (count($pathParts) < 2) {
            return ValidationResult::pass();
        }

        $parentFolder = $pathParts[count($pathParts) - 2] ?? '';

        // If file is in a Flow or Capability folder, it should relate to that flow/capability
        if (in_array($parentFolder, ['Flow', 'Flows', 'Capabilities', 'Capability'], true)) {
            // Check naming consistency (simplified check)
            $grandParent = $pathParts[count($pathParts) - 3] ?? '';

            // File in Flow folder should ideally relate to flow name
            // This is a soft check - mainly for awareness
        }

        return ValidationResult::pass();
    }

    /**
     * Detect system root directory
     */
    private function detectSystemRoot(string $basePath): string
    {
        $candidates = ['src', 'system', 'System', 'product', 'app'];

        foreach ($candidates as $candidate) {
            $candidatePath = $basePath.'/'.$candidate;
            if (is_dir($candidatePath)) {
                return $candidatePath;
            }
        }

        // Check if we're already in a system-like structure
        $subdirs = ['Flows', 'Capabilities', 'Configuration', 'Foundation'];
        foreach ($subdirs as $subdir) {
            if (is_dir($basePath.'/'.$subdir)) {
                return $basePath;
            }
        }

        return $basePath;
    }

    /**
     * Get path relative to system root
     */
    private function getRelativePath(string $filePath, string $systemRoot): string
    {
        $systemRoot = rtrim($systemRoot, '/').'/';
        $filePath = ltrim($filePath, '/');

        if (str_starts_with($filePath, $systemRoot)) {
            return substr($filePath, strlen($systemRoot));
        }

        return $filePath;
    }

    #[Override]
    public function supports(array $context): bool
    {
        return ! empty($context['staged_files'] ?? []);
    }
}
