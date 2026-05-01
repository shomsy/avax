<?php

declare(strict_types=1);

final class FreezeRecoveredComponentsTaxonomy
{
    private string $root;
    private bool $apply;

    /** @var list<array{from: string, to: string, reason: string}> */
    private array $moves = [];

    /** @var array<string, string> */
    private array $namespaceRewrites = [];

    /** @var list<string> */
    private array $conflicts = [];

    /** @var list<string> */
    private array $operations = [];

    public function __construct(array $argv)
    {
        $this->root  = getcwd() ?: throw new RuntimeException('Cannot resolve working directory.');
        $this->apply = in_array('--apply', $argv, true);

        $this->defineMoves();
        $this->defineNamespaceRewrites();
    }

    private function defineMoves() : void
    {
        $this->addMove('components/FeatureFlags', 'components/Application/FeatureFlags', 'feature flags belong to Application suite');
        $this->addMove('components/Pipeline', 'components/Application/Pipeline', 'pipeline belongs to Application suite');

        $this->addMove('components/ApiVersioning', 'components/HTTP/ApiVersioning', 'API versioning is HTTP protocol concern');
        $this->addMove('components/AfterResponse', 'components/HTTP/AfterResponse', 'after response is HTTP protocol concern');
        $this->addMove('components/ContentNegotiation', 'components/HTTP/ContentNegotiation', 'content negotiation is HTTP protocol concern');

        $this->addMove('components/Resilience', 'components/Operations/Resilience', 'resilience patterns belong to Operations suite');
        $this->addMove('components/Concurrency', 'components/Operations/Concurrency', 'concurrency belongs to Operations suite');
        $this->addMove('components/Tasks', 'components/Operations/Tasks', 'tasks belong to Operations suite');
        $this->addMove('components/Scheduler', 'components/Operations/Scheduler', 'scheduler already in Operations');
        $this->addMove('components/Realtime', 'components/Operations/Realtime', 'realtime belongs to Operations suite');
        $this->addMove('components/MessageBus', 'components/Operations/MessageBus', 'message bus belongs to Operations suite');

        $this->addMove('components/Idempotency', 'components/Operations/Resilience/System/Capabilities/Idempotency', 'idempotency is resilience capability');
        $this->addMove('components/Fallback', 'components/Operations/Resilience/System/Capabilities/Fallback', 'fallback is resilience capability');
        $this->addMove('components/TaskDispatch', 'components/Operations/Queue/System/Capabilities/TaskDispatch', 'task dispatch is queue capability');
        $this->addMove('components/Orchestration', 'components/Operations/ApplicationWorkflow/System/Capabilities/Orchestration', 'orchestration belongs to workflow capability');

        $this->addMove('components/Security', 'components/Security/System', 'Security is now a top-level suite');
        $this->addMove('components/Secrets', 'components/Security/Secrets', 'secrets belongs to Security suite');

        $this->addMove('components/Policy', 'components/Identity/Access/System/Capabilities/Policy', 'policy is access capability');
        $this->addMove('components/Tenancy', 'components/Identity/Tenancy', 'tenancy already in Identity suite');
        $this->addMove('components/JwtAuth', 'components/Identity/Tokens/System/Capabilities/JwtAuth', 'JWT is token capability');

        $this->addMove('components/HealthCheck', 'components/Operations/Observability/System/Capabilities/HealthCheck', 'health check is observability capability');
        $this->addMove('components/ScalingReadiness', 'components/DeveloperTools/Diagnostics/System/Capabilities/ScalingReadiness', 'scaling diagnostics belongs to DeveloperTools');
        $this->addMove('components/ServiceMap', 'components/Application/Container/System/Capabilities/ServiceMap', 'service map is container capability');
        $this->addMove('components/QueryGovernance', 'components/DataStack/Database/System/Capabilities/QueryGovernance', 'query governance is database capability');
        $this->addMove('components/ContractTesting', 'components/DeveloperTools/Testing/System/Capabilities/ContractTesting', 'contract testing belongs to DeveloperTools');

        $this->addMove('components/EnvironmentAwareness', 'components/Application/Config/System/Capabilities/EnvironmentAwareness', 'environment awareness is config capability');

        $this->addMove('components/WorkerManager', 'framework/System/Capabilities/WorkerManagement', 'runtime lifecycle belongs to framework');
        $this->addMove('components/ExternalState', 'framework/System/Capabilities/ExternalState', 'external state belongs to framework');
        $this->addMove('components/StatelessBoundary', 'framework/System/Capabilities/RuntimeSafety/StatelessBoundary', 'runtime safety belongs to framework');
        $this->addMove('components/GracefulShutdown', 'framework/System/Capabilities/Runtime/GracefulShutdown', 'runtime lifecycle belongs to framework');
        $this->addMove('components/ResourceGovernor', 'framework/System/Capabilities/ResourceGovernance', 'resource governance belongs to framework');
    }

    private function addMove(string $from, string $to, string $reason) : void
    {
        $this->moves[] = ['from' => $from, 'to' => $to, 'reason' => $reason];
    }

    private function defineNamespaceRewrites() : void
    {
        $this->namespaceRewrites = [
            'Avax\\Components\\FeatureFlags'         => 'Avax\\Components\\Application\\FeatureFlags',
            'Avax\\Components\\Pipeline'             => 'Avax\\Components\\Application\\Pipeline',
            'Avax\\Components\\ApiVersioning'        => 'Avax\\Components\\HTTP\\ApiVersioning',
            'Avax\\Components\\AfterResponse'        => 'Avax\\Components\\HTTP\\AfterResponse',
            'Avax\\Components\\ContentNegotiation'   => 'Avax\\Components\\HTTP\\ContentNegotiation',
            'Avax\\Components\\Resilience'           => 'Avax\\Components\\Operations\\Resilience',
            'Avax\\Components\\Concurrency'          => 'Avax\\Components\\Operations\\Concurrency',
            'Avax\\Components\\Tasks'                => 'Avax\\Components\\Operations\\Tasks',
            'Avax\\Components\\Scheduler'            => 'Avax\\Components\\Operations\\Scheduler',
            'Avax\\Components\\Realtime'             => 'Avax\\Components\\Operations\\Realtime',
            'Avax\\Components\\MessageBus'           => 'Avax\\Components\\Operations\\MessageBus',
            'Avax\\Components\\Idempotency'          => 'Avax\\Components\\Operations\\Resilience\\System\\Capabilities\\Idempotency',
            'Avax\\Components\\Fallback'             => 'Avax\\Components\\Operations\\Resilience\\System\\Capabilities\\Fallback',
            'Avax\\Components\\TaskDispatch'         => 'Avax\\Components\\Operations\\Queue\\System\\Capabilities\\TaskDispatch',
            'Avax\\Components\\Orchestration'        => 'Avax\\Components\\Operations\\ApplicationWorkflow\\System\\Capabilities\\Orchestration',
            'Avax\\Components\\Security'             => 'Avax\\Components\\Security\\System',
            'Avax\\Components\\Secrets'              => 'Avax\\Components\\Security\\Secrets',
            'Avax\\Components\\Policy'               => 'Avax\\Components\\Identity\\Access\\System\\Capabilities\\Policy',
            'Avax\\Components\\Tenancy'              => 'Avax\\Components\\Identity\\Tenancy',
            'Avax\\Components\\JwtAuth'              => 'Avax\\Components\\Identity\\Tokens\\System\\Capabilities\\JwtAuth',
            'Avax\\Components\\HealthCheck'          => 'Avax\\Components\\Operations\\Observability\\System\\Capabilities\\HealthCheck',
            'Avax\\Components\\ScalingReadiness'     => 'Avax\\Components\\DeveloperTools\\Diagnostics\\System\\Capabilities\\ScalingReadiness',
            'Avax\\Components\\ServiceMap'           => 'Avax\\Components\\Application\\Container\\System\\Capabilities\\ServiceMap',
            'Avax\\Components\\QueryGovernance'      => 'Avax\\Components\\DataStack\\Database\\System\\Capabilities\\QueryGovernance',
            'Avax\\Components\\ContractTesting'      => 'Avax\\Components\\DeveloperTools\\Testing\\System\\Capabilities\\ContractTesting',
            'Avax\\Components\\EnvironmentAwareness' => 'Avax\\Components\\Application\\Config\\System\\Capabilities\\EnvironmentAwareness',
        ];

        uksort($this->namespaceRewrites, static fn (string $left, string $right) : int => strlen($right) <=> strlen($left));
    }

    public function run() : int
    {
        $this->assertRepoRoot();

        $this->printHeader();
        $this->detectConflicts();

        if ($this->conflicts !== []) {
            $this->writeReport(status: 'CONFLICTS');
            $this->printConflicts();

            return 2;
        }

        foreach ($this->moves as $move) {
            $this->moveDirectory(
                from  : $this->path($move['from']),
                to    : $this->path($move['to']),
                reason: $move['reason'],
            );
        }

        $this->rewriteNamespaces();
        $this->updateCheckerConfig();
        $this->writeReport(status: 'OK');

        echo PHP_EOL;
        echo $this->apply
            ? "✅ Recovered components taxonomy freeze applied.\n"
            : "✅ Dry-run complete. No files changed.\n";

        echo "Report: Code-Review-And-ToDo/component-taxonomy/recovered-components-taxonomy-report.md\n";

        if (! $this->apply) {
            echo PHP_EOL;
            echo "Ako je report čist, pokreni:\n";
            echo "php tooling/refactor/freeze-recovered-components-taxonomy.php --apply\n";
        }

        return 0;
    }

    private function assertRepoRoot() : void
    {
        foreach (['components', 'framework'] as $required) {
            if (! is_dir($this->path($required))) {
                throw new RuntimeException("Run from AvaX repo root. Missing {$required}/");
            }
        }
    }

    private function path(string $path) : string
    {
        return $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    private function printHeader() : void
    {
        echo "AvaX Recovered Components Taxonomy Freeze\n";
        echo 'Mode: ' . ($this->apply ? 'APPLY' : 'DRY-RUN') . "\n";
        echo "Tests: untouched\n";
        echo "Production namespace rewrite: yes\n\n";
    }

    private function detectConflicts() : void
    {
        foreach ($this->moves as $move) {
            $from = $this->path($move['from']);
            $to   = $this->path($move['to']);

            if (! is_dir($from) && ! is_file($from)) {
                continue;
            }

            if ($this->samePath($from, $to)) {
                continue;
            }

            $this->detectMoveConflict($from, $to);
        }
    }

    private function samePath(string $left, string $right) : bool
    {
        return rtrim($left, DIRECTORY_SEPARATOR) === rtrim($right, DIRECTORY_SEPARATOR);
    }

    private function detectMoveConflict(string $from, string $to) : void
    {
        if (is_file($from)) {
            if (is_dir($to)) {
                $this->conflicts[] = "File would overwrite directory: {$this->relative($from)} -> {$this->relative($to)}";

                return;
            }

            if (is_file($to) && ! $this->sameFile($from, $to)) {
                $this->conflicts[] = "Different target file exists: {$this->relative($from)} -> {$this->relative($to)}";
            }

            return;
        }

        if (! is_dir($from)) {
            return;
        }

        if (is_file($to)) {
            $this->conflicts[] = "Directory would overwrite file: {$this->relative($from)} -> {$this->relative($to)}";

            return;
        }

        foreach ($this->children($from) as $child) {
            $this->detectMoveConflict($from . DIRECTORY_SEPARATOR . $child, $to . DIRECTORY_SEPARATOR . $child);
        }
    }

    private function relative(string $path) : string
    {
        return ltrim(str_replace($this->root, '', $path), DIRECTORY_SEPARATOR);
    }

    private function sameFile(string $left, string $right) : bool
    {
        if (! is_file($left) || ! is_file($right)) {
            return false;
        }

        return filesize($left) === filesize($right)
            && hash_file('sha256', $left) === hash_file('sha256', $right);
    }

    private function children(string $directory) : array
    {
        $items = scandir($directory);

        if ($items === false) {
            return [];
        }

        return array_values(array_filter($items, static fn (string $item) : bool => $item !== '.' && $item !== '..'));
    }

    private function writeReport(string $status) : void
    {
        $report    = $this->path('Code-Review-And-ToDo/component-taxonomy/recovered-components-taxonomy-report.md');
        $directory = dirname($report);

        if (! is_dir($directory)) {
            mkdir($directory, 0o777, true);
        }

        $lines = [
            '# Recovered Components Taxonomy Report',
            '',
            '- Date: ' . date('Y-m-d H:i:s'),
            '- Mode: ' . ($this->apply ? 'APPLY' : 'DRY-RUN'),
            '- Status: ' . $status,
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
        $lines[] = '## Conflicts';
        $lines[] = '';

        if ($this->conflicts === []) {
            $lines[] = '- none';
        } else {
            foreach ($this->conflicts as $conflict) {
                $lines[] = '- ' . $conflict;
            }
        }

        $lines[] = '';
        $lines[] = '## Notes';
        $lines[] = '';
        $lines[] = '- Tests were not modified.';
        $lines[] = '- Security is now a real suite in check-component-suite-structure.php.';
        $lines[] = '- Runtime lifecycle moved to framework/System/Capabilities/.';
        $lines[] = '- Run composer dump-autoload after apply.';
        $lines[] = '- Run architecture checkers after apply.';

        file_put_contents($report, implode(PHP_EOL, $lines) . PHP_EOL);
    }

    private function printConflicts() : void
    {
        echo PHP_EOL;
        echo '❌ Conflicts found. Nothing was moved.' . PHP_EOL;

        foreach ($this->conflicts as $conflict) {
            echo " - {$conflict}" . PHP_EOL;
        }

        echo PHP_EOL;
        echo 'Fix conflicts manually or inspect the report before applying.' . PHP_EOL;
    }

    private function moveDirectory(string $from, string $to, string $reason) : void
    {
        if (! is_dir($from) && ! is_file($from)) {
            return;
        }

        if ($this->samePath($from, $to)) {
            return;
        }

        $operation = sprintf(
            '%s `%s` -> `%s` | %s',
            is_dir($to) ? 'MERGE' : 'MOVE',
            $this->relative($from),
            $this->relative($to),
            $reason,
        );

        $this->operations[] = $operation;

        echo ($this->apply ? '' : '[dry-run] ') . $operation . PHP_EOL;

        if (! $this->apply) {
            return;
        }

        $this->mergeMove($from, $to);
    }

    private function mergeMove(string $from, string $to) : void
    {
        if (is_file($from)) {
            $this->moveFile($from, $to);

            return;
        }

        if (! is_dir($from)) {
            return;
        }

        if (! is_dir($to)) {
            $parent = dirname($to);

            if (! is_dir($parent)) {
                mkdir($parent, 0o777, true);
            }

            rename($from, $to);

            return;
        }

        foreach ($this->children($from) as $child) {
            $this->mergeMove($from . DIRECTORY_SEPARATOR . $child, $to . DIRECTORY_SEPARATOR . $child);
        }

        $this->removeDirectoryIfEmpty($from);
    }

    private function moveFile(string $from, string $to) : void
    {
        $parent = dirname($to);

        if (! is_dir($parent)) {
            mkdir($parent, 0o777, true);
        }

        if (is_file($to)) {
            if ($this->sameFile($from, $to)) {
                unlink($from);

                return;
            }

            throw new RuntimeException("Refusing to overwrite different file: {$this->relative($to)}");
        }

        rename($from, $to);
    }

    private function removeDirectoryIfEmpty(string $directory) : void
    {
        if (is_dir($directory) && $this->children($directory) === []) {
            rmdir($directory);
        }
    }

    private function rewriteNamespaces() : void
    {
        $paths = [
            $this->path('components'),
            $this->path('framework'),
            $this->path('bin'),
            $this->path('config'),
        ];

        $changed = 0;

        foreach ($paths as $path) {
            if (! file_exists($path)) {
                continue;
            }

            foreach ($this->phpFiles($path) as $file) {
                $content = file_get_contents($file);

                if ($content === false) {
                    continue;
                }

                $updated = $content;

                foreach ($this->namespaceRewrites as $old => $new) {
                    $pattern = '/(?<![A-Za-z0-9_\\\\])' . preg_quote($old, '/') . '(?=\\\\|;|,|\\)|\\s|$)/';
                    $updated = preg_replace($pattern, str_replace('\\', '\\\\', $new), $updated) ?? $updated;
                }

                if ($updated === $content) {
                    continue;
                }

                $operation          = 'REWRITE `' . $this->relative($file) . '`';
                $this->operations[] = $operation;

                echo ($this->apply ? '' : '[dry-run] ') . $operation . PHP_EOL;

                if ($this->apply) {
                    file_put_contents($file, $updated);
                }

                $changed++;
            }
        }

        echo PHP_EOL . "Namespace rewrite candidates: {$changed}" . PHP_EOL;
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

    private function updateCheckerConfig() : void
    {
        if (! $this->apply) {
            return;
        }

        $checkerFile = $this->path('tooling/refactor/check-component-suite-structure.php');

        if (! file_exists($checkerFile)) {
            return;
        }

        $content = file_get_contents($checkerFile);

        if ($content === false) {
            return;
        }

        $updated = $content;

        if (! str_contains($content, "'Security'")) {
            $updated = preg_replace(
                "/private array \\$allowedSuites = \\[\n(?:.*'DeveloperTools',\n?)/",
                "$1        'Security',\n",
                $updated,
            );
        }

        $newHelpers = "'Security', 'WorkerManagement', 'ExternalState', 'RuntimeSafety', 'ResourceGovernance'";

        if (! str_contains($content, "'WorkerManagement'")) {
            $updated = preg_replace(
                "/private array \\$helperFolders = \\[\n(?:.*'Security',\n?)/",
                '$1        ' . $newHelpers . ",\n",
                $updated,
            );
        }

        if ($updated !== $content) {
            $operation          = 'UPDATE checker config';
            $this->operations[] = $operation;
            echo ($this->apply ? '' : '[dry-run] ') . $operation . PHP_EOL;
            file_put_contents($checkerFile, $updated);
        }
    }
}

try {
    exit((new FreezeRecoveredComponentsTaxonomy($argv))->run());
} catch (Throwable $exception) {
    fwrite(STDERR, 'ERROR: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
