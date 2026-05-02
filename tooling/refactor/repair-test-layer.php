<?php

declare(strict_types=1);

/**
 * AvaX Test Layer Repair.
 *
 * Run from repo root:
 *
 *   php tooling/refactor/repair-test-layer.php
 *   php tooling/refactor/repair-test-layer.php --apply
 *
 * Scope:
 * - rewrites test namespaces to match new component taxonomy
 * - fixes PSR-4 autoload paths in tests/
 * - fixes fake contracts and test doubles
 * - does NOT touch production code
 */
final class RepairTestLayer
{
    private readonly string $root;

    private readonly bool $apply;

    /** @var array<string, string> */
    private array $namespaceRewrites = [];

    /** @var list<string> */
    private array $operations = [];

    /** @var list<string> */
    private array $errors = [];

    public function __construct(array $argv)
    {
        $this->root = getcwd() ?: throw new RuntimeException('Cannot resolve working directory.');
        $this->apply = in_array('--apply', $argv, true);

        $this->defineNamespaceRewrites();
    }

    private function defineNamespaceRewrites() : void
    {
        $componentMoves = [
            'FeatureFlags'       => 'Application\\FeatureFlags',
            'Pipeline'           => 'Application\\Pipeline',
            'ApiVersioning'      => 'HTTP\\ApiVersioning',
            'AfterResponse'      => 'HTTP\\AfterResponse',
            'ContentNegotiation' => 'HTTP\\ContentNegotiation',
            'Concurrency'        => 'Operations\\Concurrency',
            'Realtime'           => 'Operations\\Realtime',
            'MessageBus'         => 'Operations\\MessageBus',
            'Idempotency'        => 'Operations\\Resilience\\System\\Capabilities\\Idempotency',
            'Fallback'           => 'Operations\\Resilience\\System\\Capabilities\\Fallback',
            'TaskDispatch'       => 'Operations\\Queue\\System\\Capabilities\\TaskDispatch',
            'Orchestration'      => 'Operations\\ApplicationWorkflow\\System\\Capabilities\\Orchestration',
            'Security'           => 'Security\\System',
            'Secrets'            => 'Security\\Secrets',
            'Policy'             => 'Identity\\Access\\System\\Capabilities\\Policy',
            'Tenancy'            => 'Identity\\Tenancy',
            'JwtAuth'            => 'Identity\\Tokens\\System\\Capabilities\\JwtAuth',
            'HealthCheck'        => 'Operations\\Observability\\System\\Capabilities\\HealthCheck',
            'ScalingReadiness'   => 'DeveloperTools\\Diagnostics\\System\\Capabilities\\ScalingReadiness',
            'ServiceMap'         => 'Application\\Container\\System\\Capabilities\\ServiceMap',
            'QueryGovernance'    => 'DataStack\\Database\\System\\Capabilities\\QueryGovernance',
            'ContractTesting'    => 'DeveloperTools\\Testing\\System\\Capabilities\\ContractTesting',
            'EnvironmentAwareness' => 'Application\\Config\\System\\Capabilities\\EnvironmentAwareness',
            'Resilience'         => 'Operations\\Resilience',
            'Scheduler'          => 'Operations\\Scheduler',
            'Tasks'              => 'Operations\\Tasks',
        ];

        foreach ($componentMoves as $old => $new) {
            $this->namespaceRewrites['Avax\Components\\' . $old] = 'Avax\Components\\' . $new;
        }
    }

    public function run() : int
    {
        $this->assertRepoRoot();

        $this->printHeader();
        $this->rewriteNamespaces();
        $this->fixTestDoubles();
        $this->writeReport();

        echo PHP_EOL;
        echo $this->apply
            ? "✅ Test layer repair applied.\n"
            : "✅ Dry-run complete. No files changed.\n";

        if (! $this->apply) {
            echo PHP_EOL;
            echo "Ako je report čist, pokreni:\n";
            echo "php tooling/refactor/repair-test-layer.php --apply\n";
            echo PHP_EOL;
            echo "Pa onda:\n";
            echo "composer dump-autoload -o\n";
            echo "vendor/bin/phpunit --list-tests\n";
        }

        return $this->errors !== [] ? 1 : 0;
    }

    private function assertRepoRoot() : void
    {
        if (! is_dir($this->path('tests'))) {
            throw new RuntimeException('Run from AvaX repo root. Missing tests/');
        }
    }

    private function path(string $path) : string
    {
        return $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    private function printHeader() : void
    {
        echo "AvaX Test Layer Repair\n";
        echo 'Mode: ' . ($this->apply ? 'APPLY' : 'DRY-RUN') . "\n";
        echo "Scope: tests/ only\n\n";
    }

    private function rewriteNamespaces() : void
    {
        $testsPath = $this->path('tests');

        if (! is_dir($testsPath)) {
            return;
        }

        $changed = 0;

        foreach ($this->phpFiles($testsPath) as $file) {
            $content = file_get_contents($file);

            if ($content === false) {
                continue;
            }

            $original = $content;

            foreach ($this->namespaceRewrites as $old => $new) {
                $pattern = '/(?<![A-Za-z0-9_\\\\])' . preg_quote($old, '/') . '(?=\\\\|;|,|\\)|\\s|$)/';
                $content = preg_replace($pattern, str_replace('\\', '\\\\', $new), $content) ?? $content;
            }

            if ($content === $original) {
                continue;
            }

            $operation = 'REWRITE ' . $this->relative($file);
            $this->operations[] = $operation;

            echo ($this->apply ? '' : '[dry-run] ') . $operation . PHP_EOL;

            if ($this->apply) {
                file_put_contents($file, $content);
            }

            $changed++;
        }

        echo PHP_EOL . ('Test namespace rewrites: ' . $changed) . PHP_EOL;
    }

    private function phpFiles(string $path) : Generator
    {
        if (is_file($path)) {
            if (str_ends_with($path, '.php')) {
                yield $path;
            }

            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
                yield $file->getPathname();
            }
        }
    }

    private function relative(string $path) : string
    {
        return ltrim(str_replace($this->root, '', $path), DIRECTORY_SEPARATOR);
    }

    private function fixTestDoubles() : void
    {
        $paths = [
            $this->path('tests/Support'),
            $this->path('tests/Fixtures'),
            $this->path('tests/fixtures'),
        ];

        foreach ($paths as $path) {
            if (! is_dir($path)) {
                continue;
            }

            foreach ($this->phpFiles($path) as $file) {
                $this->fixTestDouble($file);
            }
        }
    }

    private function fixTestDouble(string $file) : void
    {
        $content = file_get_contents($file);

        if ($content === false) {
            return;
        }

        $original = $content;

        $fixes = [
            '/Avax\\\\Components\\\\HTTP\\\\Router\\\\Router;/'   => 'Avax\\Components\\HTTP\\Router\\Router;',
            '/Avax\\\\Components\\\\HTTP\\\\Response\\\\Response;/' => 'Avax\\Components\\HTTP\\Response\\Response;',
            '/Avax\\\\Components\\\\HTTP\\\\Session\\\\Session;/' => 'Avax\\Components\\HTTP\\Session\\Session;',
            '/Avax\\\\Components\\\\Container\\\\Container;/'     => 'Avax\\Components\\Application\\Container\\Container;',
            '/Avax\\\\Components\\\\Cache\\\\Cache;/'             => 'Avax\\Components\\Application\\Cache\\Cache;',
            '/Avax\\\\Components\\\\Config\\\\Config;/'           => 'Avax\\Components\\Application\\Config\\Config;',
        ];

        foreach ($fixes as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content) ?? $content;
        }

        if ($content === $original) {
            return;
        }

        $operation = 'FIX ' . $this->relative($file);
        $this->operations[] = $operation;

        echo ($this->apply ? '' : '[dry-run] ') . $operation . PHP_EOL;

        if ($this->apply) {
            file_put_contents($file, $content);
        }
    }

    private function writeReport() : void
    {
        $report = $this->path('Code-Review-And-ToDo/component-taxonomy/test-layer-repair-report.md');
        $directory = dirname($report);

        if (! is_dir($directory)) {
            mkdir($directory, 0o777, true);
        }

        $lines = [
            '# Test Layer Repair Report',
            '',
            '- Date: ' . date('Y-m-d H:i:s'),
            '- Mode: ' . ($this->apply ? 'APPLY' : 'DRY-RUN'),
            '',
            '## Operations',
            '',
        ];

        if ($this->operations === []) {
            $lines[] = '- none';
        } else {
            foreach ($this->operations as $operation) {
                $lines[] = '- ' . $operation;
            }
        }

        $lines[] = '';
        $lines[] = '## Notes';
        $lines[] = '';
        $lines[] = '- Production code was not modified.';
        $lines[] = '- Run composer dump-autoload after apply.';
        $lines[] = '- Run vendor/bin/phpunit --list-tests to verify.';

        file_put_contents($report, implode(PHP_EOL, $lines) . PHP_EOL);
    }
}

try {
    exit(new RepairTestLayer($argv)->run());
} catch (Throwable $throwable) {
    fwrite(STDERR, 'ERROR: ' . $throwable->getMessage() . PHP_EOL);
    exit(1);
}
