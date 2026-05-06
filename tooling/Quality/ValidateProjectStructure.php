<?php

declare(strict_types=1);

namespace Avax\Tooling\Quality;

require_once __DIR__.'/canonical-tree-definition.php';

final class ValidateProjectStructure
{
    private readonly string $basePath;

    private array $errors = [];

    private array $warnings = [];

    private array $passed = [];

    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath ?? dirname(__DIR__, 2);
    }

    public function printReport(): void
    {
        $r = $this->validate();

        echo "=== AvaX Project Structure Validation ===\n\n";

        if ($r['passed']) {
            echo '✅ PASSED ('.count($r['passed'])."):\n";
            foreach ($r['passed'] as $p) {
                echo sprintf('  ✓ %s%s', $p, PHP_EOL);
            }

            echo "\n";
        }

        if ($r['warnings']) {
            echo '⚠️  WARNINGS ('.count($r['warnings'])."):\n";
            foreach ($r['warnings'] as $w) {
                echo sprintf('  ⚠ %s%s', $w, PHP_EOL);
            }

            echo "\n";
        }

        if ($r['errors']) {
            echo '❌ ERRORS ('.count($r['errors'])."):\n";
            foreach ($r['errors'] as $e) {
                echo sprintf('  ✗ %s%s', $e, PHP_EOL);
            }

            echo "\n";
        }

        echo "--- {$r['summary']} ---\n";
        echo sprintf('Status: %s%s', $r['status'], PHP_EOL);
    }

    public function validate(): array
    {
        $this->reset();
        $this->checkRequiredFiles();
        $this->checkV1Components();
        $this->checkForbiddenInComponents();
        $this->checkForbiddenFolderNames();
        $this->checkLabsStructure();
        $this->checkExtraRoots();

        return [
            'status' => $this->errors === [] ? 'PASS' : 'FAIL',
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'passed' => $this->passed,
            'summary' => $this->getSummary(),
        ];
    }

    private function reset(): void
    {
        $this->errors = [];
        $this->warnings = [];
        $this->passed = [];
    }

    private function checkRequiredFiles(): void
    {
        foreach (AvaxCanonicalTree::getRequiredFiles() as $file) {
            $path = $this->basePath.'/'.$file;
            if (file_exists($path)) {
                $this->passed[] = 'Required file exists: '.$file;
            } else {
                $this->errors[] = 'Missing required file: '.$file;
            }
        }
    }

    private function checkV1Components(): void
    {
        $componentsPath = $this->basePath.'/components';
        if (! is_dir($componentsPath)) {
            $this->errors[] = 'components/ directory not found';

            return;
        }

        $items = scandir($componentsPath);
        $found = [];
        foreach ($items as $item) {
            if ($item === '.') {
                continue;
            }

            if ($item === '..') {
                continue;
            }

            if (! is_dir($componentsPath.'/'.$item)) {
                continue;
            }

            if (in_array($item, ['compat.php', 'components.txt', 'new-component.md'], true)) {
                continue;
            }

            $found[] = $item;
        }

        $expected = AvaxCanonicalTree::getV1Components();
        sort($found);
        sort($expected);

        if ($found === $expected) {
            $this->passed[] = 'V1 components canonical: '.implode(', ', $expected);
        } else {
            $missing = array_diff($expected, $found);
            $extra = array_diff($found, $expected);
            if ($missing !== []) {
                $this->errors[] = 'Missing V1: '.implode(', ', $missing);
            }

            if ($extra !== []) {
                $this->errors[] = 'Extra in components/: '.implode(', ', $extra);
            }
        }
    }

    private function checkForbiddenInComponents(): void
    {
        $componentsPath = $this->basePath.'/components';
        if (! is_dir($componentsPath)) {
            return;
        }

        $items = scandir($componentsPath);
        $forbidden = AvaxCanonicalTree::getForbiddenInComponents();

        foreach ($items as $item) {
            if ($item === '.') {
                continue;
            }

            if ($item === '..') {
                continue;
            }

            if (in_array($item, $forbidden, true)) {
                $this->errors[] = 'Forbidden in components/: '.$item;
            }
        }
    }

    private function checkForbiddenFolderNames(): void
    {
        $forbidden = AvaxCanonicalTree::getForbiddenFolders();
        $dirs = ['components', 'framework', 'labs', 'benchmarks'];

        foreach ($dirs as $dir) {
            $path = $this->basePath.'/'.$dir;
            if (! is_dir($path)) {
                continue;
            }

            $this->scanDir($path, $dir, $forbidden);
        }
    }

    private function scanDir(string $dirPath, string $relative, array $forbidden): void
    {
        $items = scandir($dirPath);
        foreach ($items as $item) {
            if ($item === '.') {
                continue;
            }

            if ($item === '..') {
                continue;
            }

            if (is_dir($dirPath.'/'.$item)) {
                if (in_array($item, $forbidden, true)) {
                    $this->errors[] = sprintf("Forbidden name '%s' in %s/%s", $item, $relative, $item);
                }

                $this->scanDir($dirPath.'/'.$item, $relative.'/'.$item, $forbidden);
            }
        }
    }

    private function checkLabsStructure(): void
    {
        $labsPath = $this->basePath.'/labs';
        if (! is_dir($labsPath)) {
            $this->warnings[] = 'labs/ not found (V2 locked)';

            return;
        }

        $items = scandir($labsPath);
        $v2Labs = AvaxCanonicalTree::getV2Labs();

        foreach ($items as $item) {
            if ($item === '.') {
                continue;
            }

            if ($item === '..') {
                continue;
            }

            if (! is_dir($labsPath.'/'.$item)) {
                continue;
            }

            if (! in_array($item, $v2Labs, true)) {
                $this->warnings[] = 'Unknown lab: '.$item;
            } else {
                $this->passed[] = 'V2 lab: '.$item;
            }
        }
    }

    private function checkExtraRoots(): void
    {
        $roots = AvaxCanonicalTree::getExtraRoots();
        foreach ($roots as $root) {
            $path = $this->basePath.'/'.$root;
            if (is_dir($path)) {
                $this->passed[] = 'Extra root exists: '.$root;
            }
        }
    }

    private function getSummary(): string
    {
        return sprintf(
            'Passed: %d | Errors: %d | Warnings: %d',
            count($this->passed),
            count($this->errors),
            count($this->warnings)
        );
    }
}

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $v = new ValidateProjectStructure();
    $v->printReport();
    exit($v->validate()['status'] === 'PASS' ? 0 : 1);
}
