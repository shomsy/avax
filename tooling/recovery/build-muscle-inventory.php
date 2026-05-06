<?php

declare(strict_types=1);

/**
 * Builds the Stage V1-01 recovery inventory from concatenated backup dumps and
 * local Git refs. This is intentionally report-only: it must not restore code.
 */
final class MuscleInventoryBuilder
{
    private const HEADER_PATTERN = '/^===\s+(.+?)\s+===$/';

    /** @var list<array<string, mixed>> */
    private array $records = [];

    /** @var array<string, int> */
    private array $featureTestCounts = [];

    /** @var array<string, list<string>> */
    private array $gitLogs = [];

    /** @var array<string, int> */
    private array $sourceHeaderCounts = [];

    /** @var array<string, int> */
    private array $sourceMeaningfulCounts = [];

    /** @var array<string, true> */
    private array $seenRecordKeys = [];

    /** @var list<string> */
    private array $skippedSources = [];

    public function __construct(
        private readonly string $repoRoot,
        private readonly string $markdownOut,
        private readonly string $jsonOut
    ) {
    }

    /**
     * @param  array<string, string>  $dumpSources
     * @param  list<string>  $gitRefs
     */
    public function build(array $dumpSources, array $gitRefs): void
    {
        foreach ($dumpSources as $sourceName => $path) {
            $this->readDumpSource($sourceName, $this->repoRoot.'/'.$path);
        }

        foreach ($gitRefs as $ref) {
            $this->readGitRef($ref);
        }

        $this->attachFeatureTestEvidence();
        $this->sortRecords();
        $this->writeJson();
        $this->writeMarkdown();

        echo 'Recovery muscle inventory records: '.count($this->records).PHP_EOL;
        echo 'Markdown: '.$this->markdownOut.PHP_EOL;
        echo 'JSON: '.$this->jsonOut.PHP_EOL;
    }

    private function readDumpSource(string $sourceName, string $path): void
    {
        if (! is_file($path)) {
            $this->skippedSources[] = $sourceName.' missing at '.$path;

            return;
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            $this->skippedSources[] = $sourceName.' unreadable at '.$path;

            return;
        }

        $currentPath = null;
        $content = '';
        $lineCount = 0;

        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);
            if (preg_match(self::HEADER_PATTERN, $trimmed, $matches) === 1) {
                if ($currentPath !== null) {
                    $this->recordDumpEntry($sourceName, $currentPath, $content, $lineCount);
                }

                $currentPath = $matches[1];
                $content = '';
                $lineCount = 0;
                $this->sourceHeaderCounts[$sourceName] = ($this->sourceHeaderCounts[$sourceName] ?? 0) + 1;

                continue;
            }

            if ($currentPath !== null) {
                $content .= $line;
                $lineCount++;
            }
        }

        if ($currentPath !== null) {
            $this->recordDumpEntry($sourceName, $currentPath, $content, $lineCount);
        }

        fclose($handle);
    }

    private function recordDumpEntry(string $sourceName, string $oldPath, string $content, int $lineCount): void
    {
        $recordKey = $sourceName."\0".$oldPath."\0".sha1($content);
        if (isset($this->seenRecordKeys[$recordKey])) {
            return;
        }

        $analysis = $this->analyzeEntry($sourceName, $oldPath, $content, $lineCount, 'dump');

        if (! $analysis['meaningful']) {
            return;
        }

        $this->seenRecordKeys[$recordKey] = true;
        $this->sourceMeaningfulCounts[$sourceName] = ($this->sourceMeaningfulCounts[$sourceName] ?? 0) + 1;
        $this->records[] = $analysis;

        if ($analysis['is_test']) {
            $feature = (string) $analysis['feature'];
            $this->featureTestCounts[$feature] = ($this->featureTestCounts[$feature] ?? 0) + 1;
        }
    }

    private function readGitRef(string $ref): void
    {
        $snapshotExists = $this->gitSnapshotExists($ref);
        if (! $snapshotExists && ! $this->gitRefExists($ref)) {
            $this->skippedSources[] = 'git ref missing: '.$ref;

            return;
        }

        if ($snapshotExists) {
            $snapshot = $this->readGitSnapshot($ref);
            $paths = $snapshot['paths'];
            $this->gitLogs[$ref] = $snapshot['log'];
        } else {
            $this->gitLogs[$ref] = $this->runGitLines(['log', '--oneline', '--decorate', '-n', '40', $ref]);
            $paths = $this->runGitLines(['ls-tree', '-r', '--name-only', $ref]);
        }

        foreach ($paths as $path) {
            $recordKey = 'git:'.$ref."\0".$path;
            if (isset($this->seenRecordKeys[$recordKey])) {
                continue;
            }

            $analysis = $this->analyzeEntry('git:'.$ref, $path, '', 0, 'git-ref');
            if (! $analysis['meaningful']) {
                continue;
            }

            $this->seenRecordKeys[$recordKey] = true;
            $this->sourceMeaningfulCounts['git:'.$ref] = ($this->sourceMeaningfulCounts['git:'.$ref] ?? 0) + 1;
            $analysis['old_path'] = 'git:'.$ref.':'.$path;
            $analysis['behavior_summary'] = 'Path-level signal from local Git ref; content must be inspected before restore.';
            $analysis['tests_found'] = $analysis['is_test'] ? 'yes (git path)' : 'unknown from path-only inventory';
            $this->records[] = $analysis;

            if ($analysis['is_test']) {
                $feature = (string) $analysis['feature'];
                $this->featureTestCounts[$feature] = ($this->featureTestCounts[$feature] ?? 0) + 1;
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function analyzeEntry(string $sourceName, string $oldPath, string $content, int $lineCount, string $sourceKind): array
    {
        $normalizedPath = str_replace('\\', '/', $oldPath);
        $lowerPath = strtolower($normalizedPath);
        $extension = strtolower(pathinfo($normalizedPath, PATHINFO_EXTENSION));
        $isPhp = $extension === 'php';
        $isReport = $this->isReportLike($lowerPath, $extension);
        $isTest = $this->isTestLike($lowerPath, $content);
        $meaningful = $this->isMeaningfulPath($lowerPath, $extension, $isPhp, $isReport, $isTest);

        $namespace = $this->extractNamespace($content);
        $symbols = $this->extractSymbols($content);
        $methods = $this->extractMethodNames($content);
        $feature = $this->classifyFeature($lowerPath, $content);
        $targetVersion = $this->classifyTargetVersion($feature, $lowerPath, $content, $isReport);
        $targetComponent = $this->classifyTargetComponent($feature, $lowerPath);
        $action = $this->classifyAction($targetVersion, $lowerPath, $symbols, $isReport, $sourceKind);
        $oldShape = $this->classifyOldShape($lowerPath, $symbols, $lineCount);

        return [
            'source' => $sourceName,
            'source_kind' => $sourceKind,
            'old_path' => $oldPath,
            'old_namespace' => $namespace,
            'symbols' => $symbols,
            'method_sample' => array_slice($methods, 0, 10),
            'feature' => $feature,
            'behavior_summary' => $this->summarizeBehavior($feature, $symbols, $methods, $lineCount, $oldShape, $isReport),
            'tests_found' => $isTest ? 'yes (this file)' : 'pending feature aggregation',
            'target_version' => $targetVersion,
            'target_component' => $targetComponent,
            'target_path_hint' => $this->targetPathHint($feature, $targetComponent),
            'action' => $action,
            'old_shape' => $oldShape,
            'line_count' => $lineCount,
            'byte_count' => strlen($content),
            'is_test' => $isTest,
            'is_report' => $isReport,
            'meaningful' => $meaningful,
        ];
    }

    private function isMeaningfulPath(
        string $lowerPath,
        string $extension,
        bool $isPhp,
        bool $isReport,
        bool $isTest
    ): bool {
        if ($this->isNoisePath($lowerPath)) {
            return false;
        }

        if ($isPhp || $isTest || $isReport) {
            return true;
        }

        if (in_array($extension, ['neon', 'xml', 'json', 'yaml', 'yml'], true)) {
            return $this->pathHasFeatureSignal($lowerPath);
        }

        return false;
    }

    private function isNoisePath(string $lowerPath): bool
    {
        $noisePrefixes = [
            '.aiassistant/',
            '.claude/',
            '.codex',
            '.gigaide/',
            '.idea/',
            '.kilo/',
            '.phpunit.cache/',
            '.vscode/',
            'vendor/',
            'node_modules/',
            'build/',
            'storage/',
            'tmp/',
        ];

        foreach ($noisePrefixes as $prefix) {
            if (str_starts_with($lowerPath, $prefix)) {
                return true;
            }
        }

        if (str_contains($lowerPath, '/.phpunit.cache/')) {
            return true;
        }

        return in_array($lowerPath, ['.env', '.gitignore', 'composer.lock'], true);
    }

    private function isReportLike(string $lowerPath, string $extension): bool
    {
        if (! in_array($extension, ['md', 'txt'], true)) {
            return false;
        }

        return str_contains($lowerPath, 'report')
            || str_contains($lowerPath, 'review')
            || str_contains($lowerPath, 'todo')
            || str_contains($lowerPath, 'plan')
            || str_contains($lowerPath, 'how-to')
            || str_contains($lowerPath, 'feature')
            || str_contains($lowerPath, 'missing')
            || str_contains($lowerPath, 'audit');
    }

    private function isTestLike(string $lowerPath, string $content): bool
    {
        return str_contains($lowerPath, '/test')
            || str_contains($lowerPath, 'tests/')
            || str_contains($lowerPath, 'test.php')
            || str_contains($content, 'PHPUnit\\Framework\\TestCase')
            || str_contains($content, 'extends TestCase');
    }

    private function pathHasFeatureSignal(string $lowerPath): bool
    {
        $needles = [
            'application',
            'auth',
            'cache',
            'cli',
            'config',
            'container',
            'database',
            'datastack',
            'event',
            'framework',
            'http',
            'identity',
            'mail',
            'middleware',
            'operation',
            'queue',
            'router',
            'security',
            'session',
            'validation',
            'view',
        ];

        foreach ($needles as $needle) {
            if (str_contains($lowerPath, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function extractNamespace(string $content): string
    {
        if (preg_match('/^\s*namespace\s+([^;]+);/m', $content, $matches) === 1) {
            return trim($matches[1]);
        }

        return '';
    }

    /**
     * @return list<string>
     */
    private function extractSymbols(string $content): array
    {
        if ($content === '') {
            return [];
        }

        preg_match_all(
            '/^\s*(?:final\s+|abstract\s+|readonly\s+)?(?:class|interface|trait|enum)\s+([A-Za-z_][A-Za-z0-9_]*)\b/m',
            $content,
            $matches
        );

        return array_values(array_unique($matches[1] ?? []));
    }

    /**
     * @return list<string>
     */
    private function extractMethodNames(string $content): array
    {
        if ($content === '') {
            return [];
        }

        preg_match_all('/\bfunction\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/', $content, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    private function classifyFeature(string $lowerPath, string $content): string
    {
        $lowerContent = strtolower(substr($content, 0, 20000));

        $pathRules = [
            'docs-governance' => [
                '.agents/',
                'ai prompts/',
                'EVIDENCE/',
                'current_truth.md',
                'todo.md',
                'how-to',
                'governance',
                'master-plan',
            ],
            'system-design' => ['systemdesign', 'system-design', 'systemdesignkit', 'executable-system-design'],
            'framework-runtime' => [
                'framework/system/',
                'framework/runtime',
                'worker',
                'workerman',
                'swoole',
                'roadrunner',
                'frankenphp',
                'requestscope',
                'runtime/',
            ],
            'framework-boot' => ['bootstrap', 'kernel', 'bootapplication', 'handleincominghttp', 'runapplication'],
            'identity-credentials' => ['identity/credentials', 'credential', 'passkey', 'webauthn'],
            'identity-tokens' => ['identity/tokens', '/tokens/', 'jwt', 'oauth', 'sanctum'],
            'identity-tenancy' => ['identity/tenancy', 'tenant', 'tenancy', 'multi-tenant'],
            'identity-access' => ['identity/access', 'accesscontrol', 'authorization', 'permission', 'role', 'policy'],
            'identity-auth' => ['identity/auth', 'auth/', '/auth/', 'authentication', 'login', 'logout', 'registeruser', 'change-password'],
            'datastack-database' => [
                'datastack/database',
                'database/',
                '/database/',
                'migration',
                'querybuilder',
                'schema',
                'pdo',
                'transaction',
            ],
            'datastack-persistence' => ['datastack/persistence', 'persistence/', '/persistence/', 'repository', 'orm/', 'unitofwork', 'entitymanager'],
            'datastack-data' => ['datastack/data', 'datahandling', 'datafoundation', 'collection', 'dto', '/data/'],
            'http-session' => ['http/session', '/session/', 'sessionbuilder', 'cookie', 'csrf'],
            'http-middleware' => ['http/middleware', '/middleware/'],
            'http-router' => ['http/router', '/router/', 'routing', 'routecollection'],
            'http-request-response' => [
                'http/request',
                'http/response',
                'serverrequest',
                'incomingrequest',
                'requestheaders',
                'requestedinputs',
                '/response/',
                '/request/',
                'psr-7',
            ],
            'application-container' => ['application/container', 'container/', '/container/', 'dependencyinjection', 'serviceprovider'],
            'application-cache' => ['application/cache', 'cache/', '/cache/', 'cached', 'redis', 'memcached'],
            'application-config' => ['application/config', 'config/', '/config/', 'configuration', 'environment'],
            'application-filesystem' => ['application/filesystem', 'filesystem/', '/filesystem/', 'file-system', 'storage/'],
            'application-validation' => ['application/validation', 'validation/', '/validation/', 'validator'],
            'application-datetime' => ['application/datetime', 'datetime/', 'date-time', 'clock', 'carbon', 'timezone'],
            'application-text' => ['application/text', 'text/', '/text/', '/str/', 'slug', 'studly', 'camel'],
            'security-hashing' => ['security/hashing', '/hashing/', 'bcrypt', 'argon'],
            'security-secrets' => ['security/secrets', '/secrets/', 'vault', 'keyring'],
            'security-crypto' => ['security/crypto', 'security/cryptography', '/encryption/', 'encrypt', 'decrypt', 'cipher', 'signature'],
            'operations-observability' => ['operations/observability', 'operations/monitoring', 'observability', 'monitoring', 'metric', 'trace', 'telemetry', 'health'],
            'operations-resilience' => ['operations/resilience', 'retry', 'backoff', 'circuit', 'fallback', 'bulkhead', 'timeout', 'idempot'],
            'operations-workflow' => ['operations/applicationworkflow', 'workflow', 'orchestration', 'saga'],
            'operations-events' => ['operations/events', '/events/', 'eventdispatcher', 'listener', 'subscriber'],
            'operations-queue' => ['operations/queue', '/queue/', 'taskdispatch', 'job/', '/jobs/'],
            'operations-mail-notifications' => ['operations/mail', 'operations/notifications', '/mail/', 'notification', 'smtp'],
            'operations-logging' => ['operations/logging', '/logging/', 'logger', '/log/'],
            'operations-scheduler' => ['operations/scheduler', 'scheduler', 'cron', 'schedule'],
            'presentation-view' => ['presentation/view', '/view/', 'blade', 'template'],
            'cli-console' => ['cli/console', 'cli/', '/cli/', 'console', 'command/'],
            'developer-tools' => ['developertools', 'developer-tools', 'diagnostic', 'dumpdebugger', 'codegeneration', 'testing', 'tooling/'],
        ];

        foreach ($pathRules as $feature => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($lowerPath, $needle)) {
                    return $feature;
                }
            }
        }

        $contentRules = [
            'system-design' => ['architecture validation', 'executable system design'],
            'framework-runtime' => [
                'namespace avax\\framework\\system\\runtime',
                'namespace avax\\framework\\system\\capabilities\\runtime',
                'request scope',
                'state reset',
                'runtime adapter',
            ],
            'framework-boot' => ['application kernel', 'boot application'],
            'identity-credentials' => ['namespace avax\\components\\identity\\credentials', 'namespace gemini\\identity\\credentials'],
            'identity-tokens' => ['namespace avax\\components\\identity\\tokens', 'namespace gemini\\identity\\tokens'],
            'identity-tenancy' => ['namespace avax\\components\\identity\\tenancy', 'namespace gemini\\identity\\tenancy'],
            'identity-access' => [
                'namespace avax\\components\\identity\\access',
                'namespace gemini\\auth\\application\\service\\accesscontrol',
                'authorize(',
                'permission',
                'role',
            ],
            'identity-auth' => [
                'namespace avax\\components\\identity\\auth',
                'namespace gemini\\auth',
                'authenticate(',
                'login(',
                'logout(',
                'password',
            ],
            'datastack-database' => [
                'namespace avax\\database',
                'namespace gemini\\database',
                'namespace avax\\components\\datastack\\database',
                'pdo',
                'sql',
                'migration',
                'transaction',
            ],
            'datastack-persistence' => [
                'namespace avax\\components\\datastack\\persistence',
                'namespace gemini\\persistence',
                'repository',
                'unit of work',
                'identity map',
            ],
            'datastack-data' => [
                'namespace avax\\components\\datastack\\data',
                'namespace gemini\\datahandling',
                'namespace avax\\components\\datafoundation',
                'collection',
                'data transfer object',
            ],
            'http-session' => ['namespace avax\\components\\http\\session', 'namespace gemini\\http\\session', 'session'],
            'http-router' => ['namespace avax\\components\\http\\router', 'namespace gemini\\http\\router', 'route', 'router'],
            'http-request-response' => [
                'namespace avax\\http',
                'namespace avax\\components\\http\\request',
                'namespace avax\\components\\http\\response',
                'namespace gemini\\http',
                'server request',
                'http response',
                'psr-7',
            ],
            'http-middleware' => ['middleware'],
            'application-container' => [
                'namespace avax\\components\\application\\container',
                'dependency injection',
                'service provider',
                'bind(',
                'resolve(',
            ],
            'application-cache' => [
                'namespace avax\\cache',
                'namespace avax\\components\\application\\cache',
                'namespace gemini\\cache',
                'cache backend',
                'cache item',
                'remember(',
            ],
            'application-config' => [
                'namespace avax\\components\\application\\config',
                'namespace gemini\\config',
                'configuration repository',
                'environment',
            ],
            'application-filesystem' => ['namespace avax\\components\\application\\filesystem', 'filesystem'],
            'application-validation' => ['namespace avax\\components\\application\\validation', 'validator'],
            'application-datetime' => ['namespace avax\\components\\application\\datetime', 'datetimeimmutable', 'timezone'],
            'application-text' => ['namespace avax\\components\\application\\text', 'slug', 'studly', 'camel case'],
            'security-hashing' => ['namespace avax\\components\\security\\hashing', 'password_hash', 'bcrypt', 'argon'],
            'security-secrets' => ['namespace avax\\components\\security\\secrets', 'secret store', 'vault'],
            'security-crypto' => ['namespace avax\\components\\security\\cryptography', 'openssl_encrypt', 'openssl_decrypt', 'cipher'],
            'operations-events' => ['namespace avax\\components\\operations\\events', 'event dispatcher', 'event subscriber'],
            'operations-queue' => ['namespace avax\\components\\operations\\queue', 'queue worker'],
            'operations-mail-notifications' => ['smtp', 'notification'],
            'operations-logging' => ['namespace avax\\components\\operations\\logging', 'loggerinterface'],
            'operations-observability' => ['namespace avax\\components\\operations\\observability', 'metric', 'trace', 'telemetry'],
            'operations-resilience' => ['circuit breaker', 'retry policy'],
            'operations-scheduler' => ['cron'],
            'operations-workflow' => ['workflow'],
            'presentation-view' => ['namespace avax\\components\\presentation\\view', 'namespace gemini\\view', 'template engine', 'render view'],
            'cli-console' => ['console command'],
            'developer-tools' => ['diagnostic', 'tooling'],
            'docs-governance' => ['governance'],
        ];

        foreach ($contentRules as $feature => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($lowerContent, $needle)) {
                    return $feature;
                }
            }
        }

        return 'human-decision';
    }

    private function classifyTargetVersion(string $feature, string $lowerPath, string $content, bool $isReport): string
    {
        if ($this->isNoisePath($lowerPath)) {
            return 'drop';
        }

        if ($feature === 'docs-governance') {
            return $isReport ? 'V1' : 'human-decision';
        }

        if ($feature === 'system-design') {
            return 'V3';
        }

        $v2Features = [
            'identity-tenancy',
            'operations-resilience',
            'operations-workflow',
        ];

        if (in_array($feature, $v2Features, true)) {
            return 'V2';
        }

        $lowerContent = strtolower(substr($content, 0, 12000));
        if (str_contains($lowerContent, 'external service')
            || str_contains($lowerContent, 'payment gateway')
            || str_contains($lowerContent, 'webhook')
            || str_contains($lowerContent, 'stream processor')
        ) {
            return 'V2';
        }

        if ($feature === 'human-decision') {
            return 'human-decision';
        }

        return 'V1';
    }

    private function classifyTargetComponent(string $feature, string $lowerPath): string
    {
        return match ($feature) {
            'framework-runtime', 'framework-boot' => 'framework/System',
            'application-cache' => 'components/Application/Cache',
            'application-config' => 'components/Application/Config',
            'application-container' => 'components/Application/Container',
            'application-filesystem' => 'components/Application/Filesystem',
            'application-validation' => 'components/Application/Validation',
            'application-datetime' => 'components/Application/DateTime',
            'application-text' => 'components/Application/Text',
            'datastack-database' => 'components/DataStack/Database',
            'datastack-persistence' => 'components/DataStack/Persistence',
            'datastack-data' => 'components/DataStack/Data',
            'http-router' => 'components/HTTP/Router',
            'http-request-response' => str_contains($lowerPath, 'response') ? 'components/HTTP/Response' : 'components/HTTP/Request',
            'http-middleware' => 'components/HTTP/Middleware',
            'http-session' => 'components/HTTP/Session',
            'identity-auth' => 'components/Identity/Auth',
            'identity-access' => 'components/Identity/Access',
            'identity-credentials' => 'components/Identity/Credentials',
            'identity-tokens' => 'components/Identity/Tokens',
            'identity-tenancy' => 'components/Identity/Tenancy',
            'security-hashing' => 'components/Security/Hashing',
            'security-secrets' => 'components/Security/Secrets',
            'security-crypto' => 'components/Security/Cryptography',
            'operations-events' => 'components/Operations/Events',
            'operations-queue' => 'components/Operations/Queue',
            'operations-mail-notifications' => str_contains($lowerPath, 'mail') ? 'components/Operations/Mail' : 'components/Operations/Notifications',
            'operations-logging' => 'components/Operations/Logging',
            'operations-observability' => 'components/Operations/Observability',
            'operations-resilience' => 'components/Operations/Resilience',
            'operations-scheduler' => 'components/Operations/Scheduler',
            'operations-workflow' => 'components/Operations/ApplicationWorkflow',
            'presentation-view' => 'components/Presentation/View',
            'cli-console' => 'components/CLI/Console',
            'developer-tools' => 'components/DeveloperTools',
            'system-design' => 'labs/SystemDesignKit',
            'docs-governance' => 'EVIDENCE or docs',
            default => 'human-decision',
        };
    }

    /**
     * @param  list<string>  $symbols
     */
    private function classifyAction(
        string $targetVersion,
        string $lowerPath,
        array $symbols,
        bool $isReport,
        string $sourceKind
    ): string {
        if ($targetVersion === 'drop') {
            return 'drop';
        }

        if ($targetVersion === 'V2' || $targetVersion === 'V3') {
            return 'postpone';
        }

        if ($targetVersion === 'external') {
            return 'external';
        }

        if ($targetVersion === 'human-decision') {
            return 'postpone';
        }

        if ($sourceKind === 'git-ref') {
            return 'postpone';
        }

        if ($isReport) {
            return 'bridge';
        }

        if (str_contains($lowerPath, 'facade') || str_contains($lowerPath, 'publicsurface') || str_ends_with($lowerPath, 'functions.php')) {
            return 'wrap';
        }

        foreach ($symbols as $symbol) {
            if (preg_match('/(Service|Manager|Helper|Util|Utils|Repository)$/', $symbol) === 1) {
                return 'slice';
            }
        }

        if (str_contains($lowerPath, '/service/') || str_contains($lowerPath, '/helpers/') || str_contains($lowerPath, '/utils/')) {
            return 'slice';
        }

        return 'restore';
    }

    /**
     * @param  list<string>  $symbols
     */
    private function classifyOldShape(string $lowerPath, array $symbols, int $lineCount): string
    {
        if ($lineCount >= 300) {
            return 'legacy-monolith';
        }

        if (str_contains($lowerPath, 'facade')) {
            return 'old-facade';
        }

        if (str_contains($lowerPath, 'helper') || str_contains($lowerPath, '/helpers/')) {
            return 'old-helper';
        }

        if (str_contains($lowerPath, '/service/') || str_contains($lowerPath, '/services/')) {
            return 'old-service-layer';
        }

        foreach ($symbols as $symbol) {
            if (preg_match('/(Service|Manager|Helper|Util|Utils)$/', $symbol) === 1) {
                return 'old-generic-responsibility';
            }
        }

        return 'candidate-muscle';
    }

    /**
     * @param  list<string>  $symbols
     * @param  list<string>  $methods
     */
    private function summarizeBehavior(
        string $feature,
        array $symbols,
        array $methods,
        int $lineCount,
        string $oldShape,
        bool $isReport
    ): string {
        if ($isReport) {
            return 'Report or governance evidence for '.$feature.'; preserve as planning context.';
        }

        $symbolText = $symbols === [] ? 'no top-level symbol detected' : implode(', ', array_slice($symbols, 0, 3));
        $methodText = $methods === [] ? 'no method sample' : implode(', ', array_slice($methods, 0, 5));

        return sprintf(
            '%s signal; symbols: %s; actions: %s; old shape: %s; lines: %d.',
            $feature,
            $symbolText,
            $methodText,
            $oldShape,
            $lineCount
        );
    }

    private function targetPathHint(string $feature, string $targetComponent): string
    {
        if ($targetComponent === 'human-decision') {
            return 'requires architecture decision before restore';
        }

        if ($feature === 'docs-governance') {
            return $targetComponent;
        }

        if ($feature === 'system-design') {
            return 'labs/SystemDesignKit/';
        }

        if ($targetComponent === 'framework/System') {
            return 'framework/System/{PublicSurface,Flows,Capabilities,Configuration,Foundation}/';
        }

        return $targetComponent.'/System/{PublicSurface,Flows,Capabilities,Configuration,Foundation}/';
    }

    private function attachFeatureTestEvidence(): void
    {
        foreach ($this->records as &$record) {
            if ($record['tests_found'] !== 'pending feature aggregation') {
                continue;
            }

            $feature = (string) $record['feature'];
            $count = $this->featureTestCounts[$feature] ?? 0;
            $record['tests_found'] = $count > 0 ? 'yes ('.$count.' feature test files)' : 'none-detected';
        }
        unset($record);
    }

    private function sortRecords(): void
    {
        usort(
            $this->records,
            static function (array $left, array $right): int {
                return [$left['target_version'], $left['feature'], $left['source'], $left['old_path']]
                    <=> [$right['target_version'], $right['feature'], $right['source'], $right['old_path']];
            }
        );
    }

    private function writeJson(): void
    {
        $this->ensureParentDirectory($this->jsonOut);

        $payload = [
            'generated_at' => gmdate('c'),
            'stage' => 'Stage V1-01 Backup Muscle Inventory',
            'mode' => 'report-only',
            'rules' => [
                'No production code restored by this script.',
                'V2 and V3 implementation remains locked.',
                'Every restore candidate must be sliced into canonical AvaX owners before code changes.',
            ],
            'source_header_counts' => $this->sourceHeaderCounts,
            'source_meaningful_counts' => $this->sourceMeaningfulCounts,
            'skipped_sources' => $this->skippedSources,
            'git_logs' => $this->gitLogs,
            'records' => $this->records,
        ];

        file_put_contents($this->jsonOut, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
    }

    private function writeMarkdown(): void
    {
        $this->ensureParentDirectory($this->markdownOut);

        $lines = [
            '# Stage V1-01 Backup Muscle Inventory',
            '',
            'Status: REPORT-ONLY',
            'Date: 2026-05-05',
            '',
            'This inventory parses `avax-backup.txt`, `Framework.txt`, `components/components.txt`, and local Git refs.',
            'It does not restore or modify production code. V2 and V3 implementation remain locked.',
            '',
            '## Source Summary',
            '',
            '| Source | Headers or paths | Meaningful records |',
            '|---|---:|---:|',
        ];

        $sources = array_unique(array_merge(array_keys($this->sourceHeaderCounts), array_keys($this->sourceMeaningfulCounts)));
        sort($sources);
        foreach ($sources as $source) {
            $lines[] = sprintf(
                '| `%s` | %d | %d |',
                $source,
                $this->sourceHeaderCounts[$source] ?? 0,
                $this->sourceMeaningfulCounts[$source] ?? 0
            );
        }

        if ($this->skippedSources !== []) {
            $lines[] = '';
            $lines[] = 'Skipped sources:';
            foreach ($this->skippedSources as $source) {
                $lines[] = '- '.$source;
            }
        }

        $lines[] = '';
        $lines[] = '## Version Summary';
        $lines[] = '';
        $lines[] = '| Target version | Records |';
        $lines[] = '|---|---:|';
        foreach ($this->countBy('target_version') as $value => $count) {
            $lines[] = sprintf('| `%s` | %d |', $value, $count);
        }

        $lines[] = '';
        $lines[] = '## Feature Summary';
        $lines[] = '';
        $lines[] = '| Feature | Records | Tests found | Main target | Main action |';
        $lines[] = '|---|---:|---:|---|---|';
        foreach ($this->featureSummary() as $row) {
            $lines[] = sprintf(
                '| `%s` | %d | %d | `%s` | `%s` |',
                $row['feature'],
                $row['records'],
                $row['tests'],
                $row['target_component'],
                $row['action']
            );
        }

        if ($this->gitLogs !== []) {
            $lines[] = '';
            $lines[] = '## Local Git History Signals';
            foreach ($this->gitLogs as $ref => $entries) {
                $lines[] = '';
                $lines[] = '### `'.$ref.'`';
                $lines[] = '';
                foreach (array_slice($entries, 0, 12) as $entry) {
                    $lines[] = '- `'.$entry.'`';
                }
            }
        }

        $lines[] = '';
        $lines[] = '## Required Inventory Table';
        $lines[] = '';
        $lines[] = '| Old path | Old namespace | Feature | Behavior summary | Tests found | Target version | Target component | Action |';
        $lines[] = '|---|---|---|---|---|---|---|---|';

        foreach ($this->records as $record) {
            $lines[] = sprintf(
                '| `%s` | `%s` | `%s` | %s | %s | `%s` | `%s` | `%s` |',
                $this->escapeTable($this->displayOldPath($record)),
                $this->escapeTable((string) $record['old_namespace']),
                $this->escapeTable((string) $record['feature']),
                $this->escapeTable((string) $record['behavior_summary']),
                $this->escapeTable((string) $record['tests_found']),
                $this->escapeTable((string) $record['target_version']),
                $this->escapeTable((string) $record['target_component']),
                $this->escapeTable((string) $record['action'])
            );
        }

        $lines[] = '';
        $lines[] = '## Acceptance';
        $lines[] = '';
        $lines[] = '- Every parsed meaningful backup/source record is classified.';
        $lines[] = '- Legacy monolith, old facade, old helper, and old service-layer shapes are marked in JSON under `old_shape`.';
        $lines[] = '- V2 and V3 candidates are planning-only and marked `postpone`.';
        $lines[] = '- No production code was restored by this stage.';
        $lines[] = '';

        file_put_contents($this->markdownOut, implode(PHP_EOL, $lines).PHP_EOL);
    }

    /**
     * @return array<string, int>
     */
    private function countBy(string $key): array
    {
        $counts = [];
        foreach ($this->records as $record) {
            $value = (string) $record[$key];
            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }

        ksort($counts);

        return $counts;
    }

    /**
     * @return list<array{feature: string, records: int, tests: int, target_component: string, action: string}>
     */
    private function featureSummary(): array
    {
        $summary = [];
        foreach ($this->records as $record) {
            $feature = (string) $record['feature'];
            if (! isset($summary[$feature])) {
                $summary[$feature] = [
                    'feature' => $feature,
                    'records' => 0,
                    'tests' => 0,
                    'targets' => [],
                    'actions' => [],
                ];
            }

            $summary[$feature]['records']++;
            if ($record['is_test']) {
                $summary[$feature]['tests']++;
            }

            $target = (string) $record['target_component'];
            $action = (string) $record['action'];
            $summary[$feature]['targets'][$target] = ($summary[$feature]['targets'][$target] ?? 0) + 1;
            $summary[$feature]['actions'][$action] = ($summary[$feature]['actions'][$action] ?? 0) + 1;
        }

        $rows = [];
        foreach ($summary as $feature => $data) {
            arsort($data['targets']);
            arsort($data['actions']);
            $rows[] = [
                'feature' => $feature,
                'records' => $data['records'],
                'tests' => $data['tests'],
                'target_component' => array_key_first($data['targets']) ?? 'human-decision',
                'action' => array_key_first($data['actions']) ?? 'postpone',
            ];
        }

        usort($rows, static fn (array $left, array $right): int => $left['feature'] <=> $right['feature']);

        return $rows;
    }

    private function escapeTable(string $value): string
    {
        $value = str_replace(["\r", "\n"], ' ', $value);
        $value = str_replace('|', '\\|', $value);

        return trim($value) === '' ? '&nbsp;' : $value;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function displayOldPath(array $record): string
    {
        $oldPath = (string) $record['old_path'];
        if (str_starts_with($oldPath, 'git:')) {
            return $oldPath;
        }

        return (string) $record['source'].':'.$oldPath;
    }

    private function gitRefExists(string $ref): bool
    {
        $output = $this->runGitLines(['rev-parse', '--verify', $ref]);

        return $output !== [];
    }

    private function gitSnapshotExists(string $ref): bool
    {
        $safe = $this->safeRefName($ref);
        $base = $this->repoRoot . '/EVIDENCE/muscle-recovery/git-sources/' . $safe;

        return is_file($base.'.paths') || is_file($base.'.log');
    }

    /**
     * @return array{paths: list<string>, log: list<string>}
     */
    private function readGitSnapshot(string $ref): array
    {
        $safe = $this->safeRefName($ref);
        $base = $this->repoRoot . '/EVIDENCE/muscle-recovery/git-sources/' . $safe;

        return [
            'paths' => $this->readLineFile($base.'.paths'),
            'log' => $this->readLineFile($base.'.log'),
        ];
    }

    /**
     * @return list<string>
     */
    private function readLineFile(string $path): array
    {
        if (! is_file($path)) {
            return [];
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return [];
        }

        return array_values(array_map(static fn (string $line): string => trim($line), $lines));
    }

    private function safeRefName(string $ref): string
    {
        return preg_replace('/[^A-Za-z0-9._-]+/', '_', $ref) ?? $ref;
    }

    /**
     * @param  list<string>  $args
     * @return list<string>
     */
    private function runGitLines(array $args): array
    {
        $command = array_merge(['git'], $args);
        $descriptorSpec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptorSpec, $pipes, $this->repoRoot);
        if (! is_resource($process)) {
            return [];
        }

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $status = proc_close($process);

        if ($status !== 0) {
            $message = trim((string) $stderr);
            if ($message !== '') {
                $this->skippedSources[] = 'git command failed: git '.implode(' ', $args).' :: '.$message;
            }

            return [];
        }

        $lines = preg_split('/\R/', trim((string) $stdout));
        if ($lines === false || $lines === ['']) {
            return [];
        }

        return array_values(array_filter($lines, static fn (string $line): bool => trim($line) !== ''));
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
$builder = new MuscleInventoryBuilder(
    $repoRoot,
    $repoRoot . '/EVIDENCE/muscle-recovery/backup-muscle-inventory.md',
    $repoRoot . '/EVIDENCE/muscle-recovery/backup-muscle-inventory.json'
);

$builder->build(
    [
        'avax-backup.txt' => 'avax-backup.txt',
        'Framework.txt' => 'Framework.txt',
        'components/components.txt' => 'components/components.txt',
    ],
    [
        'origin/main',
        'origin/master',
        'origin/feature/avax-master-plan',
        'master',
    ]
);
