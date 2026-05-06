<?php

declare(strict_types=1);

/**
 * Stage V1-02 report-only current tree vs recovered muscle audit.
 */
final class ComponentMuscleAuditBuilder
{
    /** @var array<string, array<string, mixed>> */
    private array $components = [];

    /** @var array<string, int> */
    private array $stateCounts = [];

    /** @var array<string, list<string>> */
    private array $currentSymbolsByTarget = [];

    /** @var array<string, int> */
    private array $currentPhpFilesByTarget = [];

    /** @var array<string, int> */
    private array $currentTestFilesByTarget = [];

    /** @var list<string> */
    private array $canonicalTargets = [
        'framework/System',
        'components/Application/Cache',
        'components/Application/Config',
        'components/Application/Container',
        'components/Application/DateTime',
        'components/Application/Facade',
        'components/Application/FeatureFlags',
        'components/Application/Filesystem',
        'components/Application/Localization',
        'components/Application/Pipeline',
        'components/Application/Text',
        'components/Application/Validation',
        'components/CLI/Console',
        'components/DataStack/Data',
        'components/DataStack/Database',
        'components/DataStack/Persistence',
        'components/DeveloperTools',
        'components/HTTP/AfterResponse',
        'components/HTTP/ApiVersioning',
        'components/HTTP/Client',
        'components/HTTP/ContentNegotiation',
        'components/HTTP/Context',
        'components/HTTP/Dispatcher',
        'components/HTTP/Middleware',
        'components/HTTP/Request',
        'components/HTTP/Response',
        'components/HTTP/Router',
        'components/HTTP/Security',
        'components/HTTP/Session',
        'components/HTTP/URI',
        'components/Identity/Access',
        'components/Identity/Auth',
        'components/Identity/Credentials',
        'components/Identity/ExternalIdentity',
        'components/Identity/Security',
        'components/Identity/Tenancy',
        'components/Identity/Tokens',
        'components/Operations/ApplicationWorkflow',
        'components/Operations/Concurrency',
        'components/Operations/Delivery',
        'components/Operations/Events',
        'components/Operations/Filesystem',
        'components/Operations/Logging',
        'components/Operations/Mail',
        'components/Operations/MemoryLifecycle',
        'components/Operations/MessageBus',
        'components/Operations/Notifications',
        'components/Operations/Observability',
        'components/Operations/Queue',
        'components/Operations/Realtime',
        'components/Operations/Resilience',
        'components/Operations/RuntimeSupervision',
        'components/Operations/Scheduler',
        'components/Operations/Tasks',
        'components/Presentation/View',
        'components/Security/Cryptography',
        'components/Security/Hashing',
        'components/Security/Secrets',
        'labs/SystemDesignKit',
    ];

    public function __construct(
        private readonly string $repoRoot,
        private readonly string $inventoryPath,
        private readonly string $markdownOut,
        private readonly string $jsonOut
    ) {
    }

    public function build(): void
    {
        $inventory = $this->readInventory();
        $this->scanCurrentTree();
        $this->seedCanonicalTargets();
        $this->aggregateInventory($inventory);
        $this->finalizeComponents();
        $this->writeJson();
        $this->writeMarkdown();

        echo 'Component muscle audit rows: '.count($this->components).PHP_EOL;
        echo 'Markdown: '.$this->markdownOut.PHP_EOL;
        echo 'JSON: '.$this->jsonOut.PHP_EOL;
    }

    /**
     * @return array<string, mixed>
     */
    private function readInventory(): array
    {
        if (! is_file($this->inventoryPath)) {
            throw new RuntimeException('Inventory not found: '.$this->inventoryPath);
        }

        $json = file_get_contents($this->inventoryPath);
        if ($json === false) {
            throw new RuntimeException('Inventory unreadable: '.$this->inventoryPath);
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('Inventory JSON is invalid: '.$this->inventoryPath);
        }

        return $decoded;
    }

    private function scanCurrentTree(): void
    {
        foreach (['framework', 'components', 'labs', 'tests'] as $root) {
            $path = $this->repoRoot.'/'.$root;
            if (! is_dir($path)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (! $file instanceof SplFileInfo || ! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($this->repoRoot) + 1));
                $target = $this->currentTargetForPath($relative);
                if ($target === null) {
                    continue;
                }

                if (str_starts_with($relative, 'tests/')) {
                    $this->currentTestFilesByTarget[$target] = ($this->currentTestFilesByTarget[$target] ?? 0) + 1;

                    continue;
                }

                $this->currentPhpFilesByTarget[$target] = ($this->currentPhpFilesByTarget[$target] ?? 0) + 1;
                foreach ($this->extractSymbols((string) file_get_contents($file->getPathname())) as $symbol) {
                    $this->currentSymbolsByTarget[$target][] = $symbol;
                }
            }
        }
    }

    private function currentTargetForPath(string $relative): ?string
    {
        if (str_starts_with($relative, 'framework/System/')) {
            return 'framework/System';
        }

        if (str_starts_with($relative, 'labs/SystemDesignKit/')) {
            return 'labs/SystemDesignKit';
        }

        if (str_starts_with($relative, 'components/')) {
            $parts = explode('/', $relative);
            if (count($parts) >= 3) {
                if ($parts[1] === 'DeveloperTools') {
                    return 'components/DeveloperTools';
                }

                return $parts[0].'/'.$parts[1].'/'.$parts[2];
            }
        }

        if (str_starts_with($relative, 'tests/')) {
            return $this->targetFromTestPath($relative);
        }

        return null;
    }

    private function targetFromTestPath(string $relative): ?string
    {
        $lower = strtolower($relative);
        $rules = [
            'components/Application/Cache' => ['application/cache', '/cache/'],
            'components/Application/Config' => ['application/config', '/config/'],
            'components/Application/Container' => ['application/container', '/container/'],
            'components/Application/DateTime' => ['datetime', 'clock'],
            'components/Application/Filesystem' => ['filesystem'],
            'components/Application/Text' => ['application/text', '/text/'],
            'components/Application/Validation' => ['validation'],
            'components/CLI/Console' => ['cli', 'console', 'command'],
            'components/DataStack/Data' => ['datastack/data', 'collection', 'data/'],
            'components/DataStack/Database' => ['datastack/database', 'database', 'query', 'migration'],
            'components/DataStack/Persistence' => ['persistence'],
            'components/HTTP/Router' => ['router', 'route'],
            'components/HTTP/Request' => ['request'],
            'components/HTTP/Response' => ['response'],
            'components/HTTP/Middleware' => ['middleware'],
            'components/HTTP/Session' => ['session'],
            'components/Identity/Auth' => ['auth', 'login'],
            'components/Identity/Access' => ['access', 'policy', 'role'],
            'components/Identity/Credentials' => ['credential', 'passkey'],
            'components/Identity/Tokens' => ['token', 'jwt'],
            'components/Operations/Observability' => ['observability', 'monitoring', 'metric', 'trace'],
            'components/Operations/Queue' => ['queue', 'job'],
            'components/Operations/Resilience' => ['resilience', 'retry', 'circuit'],
            'components/Presentation/View' => ['view', 'template'],
            'components/Security/Hashing' => ['hash'],
            'components/Security/Secrets' => ['secret'],
            'framework/System' => ['framework/system', 'kernel', 'runtime'],
        ];

        foreach ($rules as $target => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($lower, $needle)) {
                    return $target;
                }
            }
        }

        return null;
    }

    private function seedCanonicalTargets(): void
    {
        foreach ($this->canonicalTargets as $target) {
            $this->ensureComponent($target);
        }
    }

    /**
     * @param  array<string, mixed>  $inventory
     */
    private function aggregateInventory(array $inventory): void
    {
        $records = $inventory['records'] ?? [];
        if (! is_array($records)) {
            return;
        }

        foreach ($records as $record) {
            if (! is_array($record)) {
                continue;
            }

            $target = (string) ($record['target_component'] ?? 'human-decision');
            $target = $this->normalizeTarget($target);
            $component = &$this->ensureComponent($target);

            $component['backup_records']++;
            $version = (string) ($record['target_version'] ?? 'human-decision');
            $component['versions'][$version] = ($component['versions'][$version] ?? 0) + 1;

            if (($record['is_test'] ?? false) === true) {
                $component['backup_tests']++;
            }

            $feature = (string) ($record['feature'] ?? 'human-decision');
            $component['features'][$feature] = ($component['features'][$feature] ?? 0) + 1;

            $action = (string) ($record['action'] ?? 'postpone');
            $component['actions'][$action] = ($component['actions'][$action] ?? 0) + 1;

            $shape = (string) ($record['old_shape'] ?? 'candidate-muscle');
            $component['old_shapes'][$shape] = ($component['old_shapes'][$shape] ?? 0) + 1;

            foreach (($record['symbols'] ?? []) as $symbol) {
                if (! is_string($symbol) || $symbol === '') {
                    continue;
                }

                $component['backup_symbols'][$symbol] = ($component['backup_symbols'][$symbol] ?? 0) + 1;
            }
        }
        unset($component);
    }

    private function normalizeTarget(string $target): string
    {
        if ($target === 'Code-Review-And-ToDo or docs') {
            return 'docs/governance';
        }

        return $target;
    }

    /**
     * @return array<string, mixed>
     */
    private function &ensureComponent(string $target): array
    {
        if (! isset($this->components[$target])) {
            $this->components[$target] = [
                'component' => $target,
                'current_state' => 'unknown',
                'current_php_files' => 0,
                'current_test_files' => 0,
                'backup_records' => 0,
                'backup_tests' => 0,
                'backup_muscle' => '',
                'missing_behavior' => '',
                'target_version' => 'human-decision',
                'target_path' => $this->targetPathFor($target),
                'priority' => 'P3',
                'risk' => 'unknown',
                'features' => [],
                'versions' => [],
                'actions' => [],
                'old_shapes' => [],
                'backup_symbols' => [],
                'current_symbols' => [],
            ];
        }

        return $this->components[$target];
    }

    private function finalizeComponents(): void
    {
        foreach ($this->components as $target => &$component) {
            $component['current_php_files'] = $this->currentPhpFilesByTarget[$target] ?? 0;
            $component['current_test_files'] = $this->currentTestFilesByTarget[$target] ?? 0;
            $component['current_symbols'] = array_values(array_unique($this->currentSymbolsByTarget[$target] ?? []));

            arsort($component['features']);
            arsort($component['versions']);
            arsort($component['actions']);
            arsort($component['old_shapes']);
            arsort($component['backup_symbols']);

            $component['target_version'] = $this->versionLabel($component['versions']);
            $component['backup_muscle'] = $this->backupMuscleSummary($component);
            $component['missing_behavior'] = $this->missingBehaviorSummary($component);
            $component['current_state'] = $this->classifyState($component);
            $component['priority'] = $this->classifyPriority($target, $component);
            $component['risk'] = $this->classifyRisk($component);

            $this->stateCounts[$component['current_state']] = ($this->stateCounts[$component['current_state']] ?? 0) + 1;
        }
        unset($component);

        ksort($this->components);
        ksort($this->stateCounts);
    }

    /**
     * @param  array<string, int>  $versions
     */
    private function versionLabel(array $versions): string
    {
        if (($versions['V1'] ?? 0) > 0) {
            return 'V1';
        }

        if (($versions['V2'] ?? 0) > 0) {
            return 'V2 locked';
        }

        if (($versions['V3'] ?? 0) > 0) {
            return 'V3 locked';
        }

        return 'human-decision';
    }

    /**
     * @param  array<string, mixed>  $component
     */
    private function backupMuscleSummary(array $component): string
    {
        if ($component['backup_records'] === 0) {
            return 'no recovered backup records';
        }

        $features = implode(', ', array_slice(array_keys($component['features']), 0, 3));
        $actions = implode(', ', array_slice(array_keys($component['actions']), 0, 3));

        return sprintf(
            '%d records, %d old test files; features: %s; actions: %s',
            $component['backup_records'],
            $component['backup_tests'],
            $features === '' ? 'unknown' : $features,
            $actions === '' ? 'unknown' : $actions
        );
    }

    /**
     * @param  array<string, mixed>  $component
     */
    private function missingBehaviorSummary(array $component): string
    {
        if ($component['backup_records'] === 0) {
            return 'no backup muscle signal yet';
        }

        $currentSymbols = array_flip($component['current_symbols']);
        $missing = [];
        foreach (array_keys($component['backup_symbols']) as $symbol) {
            if (! isset($currentSymbols[$symbol])) {
                $missing[] = $symbol;
            }

            if (count($missing) >= 8) {
                break;
            }
        }

        $shapes = implode(', ', array_slice(array_keys($component['old_shapes']), 0, 3));
        $symbolText = $missing === [] ? 'no obvious symbol gap from names' : implode(', ', $missing);

        return 'candidate behavior/symbol gaps: '.$symbolText.'; old shapes: '.($shapes === '' ? 'unknown' : $shapes);
    }

    /**
     * @param  array<string, mixed>  $component
     */
    private function classifyState(array $component): string
    {
        $phpFiles = (int) $component['current_php_files'];
        $backupRecords = (int) $component['backup_records'];
        $currentTests = (int) $component['current_test_files'];
        $targetVersion = (string) $component['target_version'];

        if ($targetVersion === 'V2 locked' || $targetVersion === 'V3 locked') {
            return $phpFiles > 0 ? 'partial' : 'missing';
        }

        if ($backupRecords === 0 && $phpFiles > 0) {
            return 'unknown';
        }

        if ($backupRecords > 0 && $phpFiles === 0) {
            return 'missing';
        }

        if ($backupRecords >= 50 && $phpFiles <= 3) {
            return 'skeleton';
        }

        if ($phpFiles >= 20 && $currentTests > 0 && $backupRecords <= 10) {
            return 'muscular';
        }

        if ($phpFiles > 0 && $backupRecords > 0) {
            return 'partial';
        }

        if ($phpFiles > 0) {
            return 'stale';
        }

        return 'unknown';
    }

    /**
     * @param  array<string, mixed>  $component
     */
    private function classifyPriority(string $target, array $component): string
    {
        $p0Targets = [
            'framework/System',
            'components/Application/Container',
            'components/Application/Config',
            'components/CLI/Console',
            'components/HTTP/Request',
            'components/HTTP/Response',
            'components/HTTP/Router',
        ];

        if (in_array($target, $p0Targets, true)) {
            return 'P0';
        }

        if ((string) $component['target_version'] === 'V1' && (int) $component['backup_records'] > 0) {
            return 'P1';
        }

        if ((string) $component['target_version'] === 'V2 locked') {
            return 'P2';
        }

        return 'P3';
    }

    /**
     * @param  array<string, mixed>  $component
     */
    private function classifyRisk(array $component): string
    {
        $state = (string) $component['current_state'];
        $backupRecords = (int) $component['backup_records'];

        if (($state === 'missing' || $state === 'skeleton') && $backupRecords >= 50) {
            return 'high';
        }

        if ($state === 'partial' || $backupRecords >= 20) {
            return 'medium';
        }

        if ($state === 'muscular') {
            return 'low';
        }

        return 'unknown';
    }

    private function targetPathFor(string $target): string
    {
        if ($target === 'framework/System') {
            return 'framework/System/{PublicSurface,Flows,Capabilities,Configuration,Foundation}/';
        }

        if ($target === 'docs/governance') {
            return 'Code-Review-And-ToDo/ or docs/';
        }

        if ($target === 'human-decision') {
            return 'requires architecture decision';
        }

        if (str_starts_with($target, 'labs/')) {
            return $target.'/';
        }

        return $target.'/System/{PublicSurface,Flows,Capabilities,Configuration,Foundation}/';
    }

    /**
     * @return list<string>
     */
    private function extractSymbols(string $content): array
    {
        preg_match_all(
            '/^\s*(?:final\s+|abstract\s+|readonly\s+)?(?:class|interface|trait|enum)\s+([A-Za-z_][A-Za-z0-9_]*)\b/m',
            $content,
            $matches
        );

        return array_values(array_unique($matches[1] ?? []));
    }

    private function writeJson(): void
    {
        $this->ensureParentDirectory($this->jsonOut);
        file_put_contents(
            $this->jsonOut,
            json_encode(
                [
                    'generated_at' => gmdate('c'),
                    'stage' => 'Stage V1-02 Current Component Muscle Audit',
                    'mode' => 'report-only',
                    'state_counts' => $this->stateCounts,
                    'components' => array_values($this->components),
                ],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            ).PHP_EOL
        );
    }

    private function writeMarkdown(): void
    {
        $this->ensureParentDirectory($this->markdownOut);

        $lines = [
            '# Stage V1-02 Current Component Muscle Audit',
            '',
            'Status: REPORT-ONLY',
            'Date: 2026-05-05',
            '',
            'This audit compares the current `framework/`, `components/`, and `labs/` tree against the Stage V1-01 recovery inventory.',
            'It does not restore production code. V2 and V3 implementation remain locked.',
            '',
            '## State Summary',
            '',
            '| Current state | Components |',
            '|---|---:|',
        ];

        foreach ($this->stateCounts as $state => $count) {
            $lines[] = sprintf('| `%s` | %d |', $state, $count);
        }

        $lines[] = '';
        $lines[] = '## Required Audit Table';
        $lines[] = '';
        $lines[] = '| Component | Current state | Backup muscle | Missing behavior | Target version | Target path | Priority | Risk |';
        $lines[] = '|---|---|---|---|---|---|---|---|';

        foreach ($this->components as $component) {
            $lines[] = sprintf(
                '| `%s` | `%s` | %s | %s | `%s` | `%s` | `%s` | `%s` |',
                $this->escapeTable((string) $component['component']),
                $this->escapeTable((string) $component['current_state']),
                $this->escapeTable((string) $component['backup_muscle']),
                $this->escapeTable((string) $component['missing_behavior']),
                $this->escapeTable((string) $component['target_version']),
                $this->escapeTable((string) $component['target_path']),
                $this->escapeTable((string) $component['priority']),
                $this->escapeTable((string) $component['risk'])
            );
        }

        $lines[] = '';
        $lines[] = '## Acceptance';
        $lines[] = '';
        $lines[] = '- Every canonical component target has a state.';
        $lines[] = '- Every recovered V1 muscle group has a candidate target path.';
        $lines[] = '- V2 and V3 candidates remain locked as planning-only.';
        $lines[] = '- No production code was changed by this audit.';
        $lines[] = '';
        $lines[] = '## Next Allowed Stage';
        $lines[] = '';
        $lines[] = 'Stage V1-03: Static Integrity Closure, unless `EXECUTION.md` is updated to insert a narrower repair stage.';
        $lines[] = '';

        file_put_contents($this->markdownOut, implode(PHP_EOL, $lines).PHP_EOL);
    }

    private function escapeTable(string $value): string
    {
        $value = str_replace(["\r", "\n"], ' ', $value);
        $value = str_replace('|', '\\|', $value);

        return trim($value) === '' ? '&nbsp;' : $value;
    }

    private function ensureParentDirectory(string $path): void
    {
        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
    }
}

$repoRoot = dirname(__DIR__, 2);
$builder = new ComponentMuscleAuditBuilder(
    $repoRoot,
    $repoRoot.'/Code-Review-And-ToDo/muscle-recovery/backup-muscle-inventory.json',
    $repoRoot.'/Code-Review-And-ToDo/muscle-recovery/component-muscle-audit.md',
    $repoRoot.'/Code-Review-And-ToDo/muscle-recovery/component-muscle-audit.json'
);

$builder->build();
