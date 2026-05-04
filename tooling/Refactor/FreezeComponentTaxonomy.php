<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

use Generator;

final class FreezeComponentTaxonomy
{
    private readonly string $root;

    private readonly bool $apply;

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
        $this->root = getcwd() ?: throw new RuntimeException('Cannot resolve working directory.');
        $this->apply = in_array('--apply', $argv, true);

        $this->defineMoves();
        $this->defineNamespaceRewrites();
    }

    private function defineMoves(): void
    {
        $topLevel = [
            'components/Config' => 'components/Application/Config',
            'components/Container' => 'components/Application/Container',
            'components/Cache' => 'components/Application/Cache',
            'components/Filesystem' => 'components/Application/Filesystem',
            'components/Validation' => 'components/Application/Validation',
            'components/Text' => 'components/Application/Text',
            'components/DateTime' => 'components/Application/DateTime',
            'components/Localization' => 'components/Application/Localization',
            'components/Translation' => 'components/Application/Localization',
            'components/Lang' => 'components/Application/Localization',
            'components/Facade' => 'components/Application/Facade',
            'components/Carbon' => 'components/Application/DateTime',
            'components/Date' => 'components/Application/DateTime',
            'components/Time' => 'components/Application/DateTime',
            'components/Storage' => 'components/Application/Filesystem',

            'components/Request' => 'components/HTTP/Request',
            'components/Response' => 'components/HTTP/Response',
            'components/Router' => 'components/HTTP/Router',
            'components/Middleware' => 'components/HTTP/Middleware',
            'components/Middlewares' => 'components/HTTP/Middleware',
            'components/Session' => 'components/HTTP/Session',
            'components/Cookies' => 'components/HTTP/Cookies',
            'components/Cookie' => 'components/HTTP/Cookies',
            'components/URI' => 'components/HTTP/URI',
            'components/Url' => 'components/HTTP/URI',
            'components/Uploads' => 'components/HTTP/Uploads',
            'components/UploadedFiles' => 'components/HTTP/Uploads',
            'components/HttpContext' => 'components/HTTP/Context',
            'components/Context' => 'components/HTTP/Context',
            'components/HttpClient' => 'components/HTTP/Client',
            'components/Csrf' => 'components/HTTP/Security',
            'components/SignedUrls' => 'components/HTTP/Security',
            'components/TrustedProxy' => 'components/HTTP/Security',
            'components/TrustedHost' => 'components/HTTP/Security',
            'components/SecurityHeaders' => 'components/HTTP/Security',

            'components/Console' => 'components/CLI/Console',
            'components/Commands' => 'components/CLI/Console',
            'components/Command' => 'components/CLI/Console',

            'components/Data' => 'components/DataStack/Data',
            'components/Collections' => 'components/DataStack/Data',
            'components/Collection' => 'components/DataStack/Data',
            'components/Arr' => 'components/DataStack/Data',
            'components/Arrhae' => 'components/DataStack/Data',
            'components/DTO' => 'components/DataStack/Data',
            'components/DataTransfer' => 'components/DataStack/Data',
            'components/ObjectMapping' => 'components/DataStack/Data',
            'components/Values' => 'components/DataStack/Data',
            'components/Structures' => 'components/DataStack/Data',

            'components/Database' => 'components/DataStack/Database',
            'components/Schema' => 'components/DataStack/Database',
            'components/Migrations' => 'components/DataStack/Database',
            'components/QueryBuilder' => 'components/DataStack/Database',
            'components/Transactions' => 'components/DataStack/Database',

            'components/Persistence' => 'components/DataStack/Persistence',
            'components/ORM' => 'components/DataStack/Persistence',
            'components/Repository' => 'components/DataStack/Persistence',
            'components/EntityManager' => 'components/DataStack/Persistence',
            'components/UnitOfWork' => 'components/DataStack/Persistence',
            'components/IdentityMap' => 'components/DataStack/Persistence',

            'components/Auth' => 'components/Identity/Auth',
            'components/Authentication' => 'components/Identity/Auth',
            'components/Authorization' => 'components/Identity/Access',
            'components/Access' => 'components/Identity/Access',
            'components/Permissions' => 'components/Identity/Access',
            'components/Roles' => 'components/Identity/Access',
            'components/Policies' => 'components/Identity/Access',
            'components/Gates' => 'components/Identity/Access',

            'components/Passwords' => 'components/Identity/Credentials',
            'components/Password' => 'components/Identity/Credentials',
            'components/MFA' => 'components/Identity/Credentials',
            'components/Mfa' => 'components/Identity/Credentials',
            'components/Passkey' => 'components/Identity/Credentials',
            'components/Passkeys' => 'components/Identity/Credentials',
            'components/RecoveryCodes' => 'components/Identity/Credentials',

            'components/Tokens' => 'components/Identity/Tokens',
            'components/JWT' => 'components/Identity/Tokens',
            'components/Jwt' => 'components/Identity/Tokens',
            'components/ApiTokens' => 'components/Identity/Tokens',
            'components/AccessTokens' => 'components/Identity/Tokens',
            'components/RefreshTokens' => 'components/Identity/Tokens',

            'components/OAuth' => 'components/Identity/ExternalIdentity',
            'components/Oidc' => 'components/Identity/ExternalIdentity',
            'components/OpenIDConnect' => 'components/Identity/ExternalIdentity',
            'components/SocialLogin' => 'components/Identity/ExternalIdentity',
            'components/ExternalIdentity' => 'components/Identity/ExternalIdentity',
            'components/Federation' => 'components/Identity/ExternalIdentity',
            'components/SSO' => 'components/Identity/ExternalIdentity',
            'components/SingleSignOn' => 'components/Identity/ExternalIdentity',

            'components/Tenancy' => 'components/Identity/Tenancy',
            'components/Tenants' => 'components/Identity/Tenancy',
            'components/Membership' => 'components/Identity/Tenancy',
            'components/Invitations' => 'components/Identity/Tenancy',

            'components/Encryption' => 'components/Security/Cryptography',
            'components/Cryptography' => 'components/Security/Cryptography',
            'components/Encryptor' => 'components/Security/Cryptography',
            'components/Signer' => 'components/Security/Cryptography',
            'components/Signing' => 'components/Security/Cryptography',
            'components/Keys' => 'components/Security/Cryptography',
            'components/KeyRotation' => 'components/Security/Cryptography',
            'components/Hashing' => 'components/Security/Hashing',
            'components/Hasher' => 'components/Security/Hashing',
            'components/PasswordHashing' => 'components/Security/Hashing',
            'components/Secrets' => 'components/Security/Secrets',
            'components/Secret' => 'components/Security/Secrets',
            'components/Vault' => 'components/Security/Secrets',
            'components/Redaction' => 'components/Security/Redaction',
            'components/SecretRedaction' => 'components/Security/Redaction',
            'components/SensitiveData' => 'components/Security/Redaction',
            'components/Random' => 'components/Security/Random',
            'components/SecureRandom' => 'components/Security/Random',
            'components/SecurityAudit' => 'components/Security/Audit',
            'components/Audit' => 'components/Security/Audit',

            'components/Events' => 'components/Operations/Events',
            'components/Event' => 'components/Operations/Events',
            'components/EventDispatcher' => 'components/Operations/Events',
            'components/Listeners' => 'components/Operations/Events',
            'components/Logging' => 'components/Operations/Logging',
            'components/Logger' => 'components/Operations/Logging',
            'components/Logs' => 'components/Operations/Logging',
            'components/Mail' => 'components/Operations/Mail',
            'components/Mailer' => 'components/Operations/Mail',
            'components/Queue' => 'components/Operations/Queue',
            'components/Queues' => 'components/Operations/Queue',
            'components/Jobs' => 'components/Operations/Queue',
            'components/Bus' => 'components/Operations/Queue',
            'components/Tasks' => 'components/Operations/Queue',
            'components/Notifications' => 'components/Operations/Notifications',
            'components/Notification' => 'components/Operations/Notifications',
            'components/Scheduler' => 'components/Operations/Scheduler',
            'components/Schedule' => 'components/Operations/Scheduler',
            'components/Cron' => 'components/Operations/Scheduler',
            'components/Resilience' => 'components/Operations/Resilience',
            'components/Retry' => 'components/Operations/Resilience',
            'components/Timeout' => 'components/Operations/Resilience',
            'components/CircuitBreaker' => 'components/Operations/Resilience',
            'components/RateLimiter' => 'components/Operations/Resilience',
            'components/Observability' => 'components/Operations/Observability',
            'components/Telemetry' => 'components/Operations/Observability',
            'components/Tracing' => 'components/Operations/Observability',
            'components/Metrics' => 'components/Operations/Observability',
            'components/Timeline' => 'components/Operations/Observability',
            'components/ApplicationWorkflow' => 'components/Operations/ApplicationWorkflow',
            'components/Workflow' => 'components/Operations/ApplicationWorkflow',
            'components/Saga' => 'components/Operations/ApplicationWorkflow',
            'components/Sagas' => 'components/Operations/ApplicationWorkflow',

            'components/View' => 'components/Presentation/View',
            'components/Views' => 'components/Presentation/View',
            'components/Template' => 'components/Presentation/View',
            'components/Templates' => 'components/Presentation/View',
            'components/Blade' => 'components/Presentation/View',
            'components/BladeOne' => 'components/Presentation/View',
            'components/Renderer' => 'components/Presentation/View',

            'components/Diagnostics' => 'components/DeveloperTools/Diagnostics',
            'components/Doctor' => 'components/DeveloperTools/Diagnostics',
            'components/HealthCheck' => 'components/DeveloperTools/Diagnostics',
            'components/DumpDebugger' => 'components/DeveloperTools/DumpDebugger',
            'components/Dumper' => 'components/DeveloperTools/DumpDebugger',
            'components/Debug' => 'components/DeveloperTools/DumpDebugger',
            'components/Whoops' => 'components/DeveloperTools/DumpDebugger',
            'components/Ignition' => 'components/DeveloperTools/DumpDebugger',
            'components/ArchitectureReview' => 'components/DeveloperTools/ArchitectureReview',
            'components/ArchitectureCheck' => 'components/DeveloperTools/ArchitectureReview',
            'components/ArchitectureChecks' => 'components/DeveloperTools/ArchitectureReview',
            'components/CodeReview' => 'components/DeveloperTools/ArchitectureReview',
            'components/Testing' => 'components/DeveloperTools/Testing',
            'components/Fakes' => 'components/DeveloperTools/Testing',
            'components/TestDoubles' => 'components/DeveloperTools/Testing',
            'components/CodeGeneration' => 'components/DeveloperTools/CodeGeneration',
            'components/Generators' => 'components/DeveloperTools/CodeGeneration',
            'components/Scaffolding' => 'components/DeveloperTools/CodeGeneration',
            'components/Make' => 'components/DeveloperTools/CodeGeneration',
            'components/Profiler' => 'components/DeveloperTools/Profiler',
            'components/PerformanceProfiler' => 'components/DeveloperTools/Profiler',
        ];

        foreach ($topLevel as $from => $to) {
            $this->addMove($from, $to, 'legacy top-level component to final suite');
        }

        $this->addMove(
            'components/CLI/Console/System/Capabilities/Generators',
            'components/DeveloperTools/CodeGeneration/System/Capabilities/Generators',
            'generator logic belongs to DeveloperTools; CLI executes commands',
        );

        $this->addMove(
            'components/Identity/Auth/System/Capabilities/ExternalIdentity',
            'components/Identity/ExternalIdentity/System/Capabilities',
            'OAuth/OIDC/SSO/Federation belongs to Identity/ExternalIdentity',
        );

        $this->addMove(
            'components/Identity/Auth/System/Capabilities/Identity/Tokens',
            'components/Identity/Tokens/System/Capabilities/Tokens',
            'token lifecycle belongs to Identity/Tokens',
        );

        $this->addMove(
            'components/Identity/Auth/System/Capabilities/Identity/Mfa',
            'components/Identity/Credentials/System/Capabilities/Mfa',
            'MFA belongs to Identity/Credentials',
        );

        $this->addMove(
            'components/Identity/Auth/System/Capabilities/Identity/Passkey',
            'components/Identity/Credentials/System/Capabilities/Passkey',
            'Passkey belongs to Identity/Credentials',
        );

        $this->addMove(
            'components/Identity/Auth/System/Capabilities/Identity/RecoveryCodes',
            'components/Identity/Credentials/System/Capabilities/RecoveryCodes',
            'recovery codes belong to Identity/Credentials',
        );

        $this->addMove(
            'components/Identity/Auth/System/Capabilities/Identity/PasswordHashing',
            'components/Security/Hashing/System/Capabilities/PasswordHashing',
            'hashing belongs to framework-wide Security/Hashing',
        );

        $this->addMove(
            'components/Identity/Auth/System/Capabilities/Access',
            'components/Identity/Access/System/Capabilities',
            'authorization belongs to Identity/Access',
        );

        $this->addMove(
            'components/Identity/Auth/System/Capabilities/Tenancy',
            'components/Identity/Tenancy/System/Capabilities',
            'tenant identity belongs to Identity/Tenancy',
        );

        $this->addMove(
            'components/Operations/Logging/System/Capabilities/Telemetry',
            'components/Operations/Observability/System/Capabilities/Telemetry',
            'telemetry belongs to Operations/Observability',
        );

        $this->addMove(
            'components/Operations/Logging/System/Capabilities/Tracing',
            'components/Operations/Observability/System/Capabilities/Tracing',
            'tracing belongs to Operations/Observability',
        );

        $this->addMove(
            'components/Operations/Logging/System/Capabilities/Metrics',
            'components/Operations/Observability/System/Capabilities/Metrics',
            'metrics belongs to Operations/Observability',
        );

        $this->addMove(
            'components/Operations/Logging/System/Capabilities/Timeline',
            'components/Operations/Observability/System/Capabilities/Timeline',
            'timeline belongs to Operations/Observability',
        );
    }

    private function addMove(string $from, string $to, string $reason): void
    {
        $this->moves[] = ['from' => $from, 'to' => $to, 'reason' => $reason];
    }

    private function defineNamespaceRewrites(): void
    {
        $this->namespaceRewrites = [
            'Avax\\Components\\CLI\\Console\\System\\Capabilities\\Generators' => 'Avax\\Components\\DeveloperTools\\CodeGeneration\\System\\Capabilities\\Generators',

            'Avax\\Components\\Identity\\Auth\\System\\Capabilities\\ExternalIdentity' => 'Avax\\Components\\Identity\\ExternalIdentity\\System\\Capabilities',

            'Avax\\Components\\Identity\\Auth\\System\\Capabilities\\Identity\\Tokens' => 'Avax\\Components\\Identity\\Tokens\\System\\Capabilities\\Tokens',

            'Avax\\Components\\Identity\\Auth\\System\\Capabilities\\Identity\\Mfa' => 'Avax\\Components\\Identity\\Credentials\\System\\Capabilities\\Mfa',

            'Avax\\Components\\Identity\\Auth\\System\\Capabilities\\Identity\\Passkey' => 'Avax\\Components\\Identity\\Credentials\\System\\Capabilities\\Passkey',

            'Avax\\Components\\Identity\\Auth\\System\\Capabilities\\Identity\\RecoveryCodes' => 'Avax\\Components\\Identity\\Credentials\\System\\Capabilities\\RecoveryCodes',

            'Avax\\Components\\Identity\\Auth\\System\\Capabilities\\Identity\\PasswordHashing' => 'Avax\\Components\\Security\\Hashing\\System\\Capabilities\\PasswordHashing',

            'Avax\\Components\\Identity\\Auth\\System\\Capabilities\\Access' => 'Avax\\Components\\Identity\\Access\\System\\Capabilities',

            'Avax\\Components\\Identity\\Auth\\System\\Capabilities\\Tenancy' => 'Avax\\Components\\Identity\\Tenancy\\System\\Capabilities',

            'Avax\\Components\\Operations\\Logging\\System\\Capabilities\\Telemetry' => 'Avax\\Components\\Operations\\Observability\\System\\Capabilities\\Telemetry',

            'Avax\\Components\\Operations\\Logging\\System\\Capabilities\\Tracing' => 'Avax\\Components\\Operations\\Observability\\System\\Capabilities\\Tracing',

            'Avax\\Components\\Operations\\Logging\\System\\Capabilities\\Metrics' => 'Avax\\Components\\Operations\\Observability\\System\\Capabilities\\Metrics',

            'Avax\\Components\\Operations\\Logging\\System\\Capabilities\\Timeline' => 'Avax\\Components\\Operations\\Observability\\System\\Capabilities\\Timeline',
        ];

        uksort(
            $this->namespaceRewrites,
            static fn(string $left, string $right): int => strlen($right) <=> strlen($left),
        );
    }

    public function run(): int
    {
        $this->assertRepoRoot();
        $this->ensureFinalSuites();

        $this->printHeader();
        $this->detectConflicts();

        if ($this->conflicts !== []) {
            $this->writeReport(status: 'CONFLICTS');
            $this->printConflicts();

            return 2;
        }

        foreach ($this->moves as $move) {
            $this->moveDirectory(
                from: $this->path($move['from']),
                to: $this->path($move['to']),
                reason: $move['reason'],
            );
        }

        $this->rewriteNamespaces();
        $this->writeReport(status: 'OK');

        echo PHP_EOL;
        echo $this->apply
            ? "✅ Component taxonomy freeze applied.\n"
            : "✅ Dry-run complete. No files changed.\n";

        echo "Report: Code-Review-And-ToDo/component-taxonomy/component-taxonomy-freeze-report.md\n";

        if (!$this->apply) {
            echo PHP_EOL;
            echo "Ako je report čist, pokreni:\n";
            echo "php tooling/refactor/freeze-component-taxonomy.php --apply\n";
        }

        return 0;
    }

    private function assertRepoRoot(): void
    {
        foreach (['components', 'framework'] as $required) {
            if (!is_dir($this->path($required))) {
                throw new RuntimeException(sprintf('Run from AvaX repo root. Missing %s/', $required));
            }
        }
    }

    private function path(string $path): string
    {
        return $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    private function ensureFinalSuites(): void
    {
        foreach ([
                     'components/Application',
                     'components/HTTP',
                     'components/CLI',
                     'components/DataStack',
                     'components/Identity',
                     'components/Security',
                     'components/Operations',
                     'components/Presentation',
                     'components/DeveloperTools',
                 ] as $directory) {
            if ($this->apply && !is_dir($this->path($directory))) {
                mkdir($this->path($directory), 0o777, true);
            }
        }
    }

    private function printHeader(): void
    {
        echo "AvaX Component Taxonomy Freeze\n";
        echo 'Mode: ' . ($this->apply ? 'APPLY' : 'DRY-RUN') . "\n";
        echo "Tests: untouched\n";
        echo "Production namespace rewrite: yes\n\n";
    }

    private function detectConflicts(): void
    {
        foreach ($this->moves as $move) {
            $from = $this->path($move['from']);
            $to = $this->path($move['to']);

            if (!is_dir($from)) {
                continue;
            }

            if ($this->samePath($from, $to)) {
                continue;
            }

            $this->detectMoveConflict($from, $to);
        }
    }

    private function samePath(string $left, string $right): bool
    {
        return rtrim($left, DIRECTORY_SEPARATOR) === rtrim($right, DIRECTORY_SEPARATOR);
    }

    private function detectMoveConflict(string $from, string $to): void
    {
        if (is_file($from)) {
            if (is_dir($to)) {
                $this->conflicts[] = sprintf('File would overwrite directory: %s -> %s', $this->relative($from), $this->relative($to));

                return;
            }

            if (is_file($to) && !$this->sameFile($from, $to)) {
                $this->conflicts[] = sprintf('Different target file exists: %s -> %s', $this->relative($from), $this->relative($to));
            }

            return;
        }

        if (!is_dir($from)) {
            return;
        }

        if (is_file($to)) {
            $this->conflicts[] = sprintf('Directory would overwrite file: %s -> %s', $this->relative($from), $this->relative($to));

            return;
        }

        foreach ($this->children($from) as $child) {
            $this->detectMoveConflict($from . DIRECTORY_SEPARATOR . $child, $to . DIRECTORY_SEPARATOR . $child);
        }
    }

    private function relative(string $path): string
    {
        return ltrim(str_replace($this->root, '', $path), DIRECTORY_SEPARATOR);
    }

    private function sameFile(string $left, string $right): bool
    {
        if (!is_file($left) || !is_file($right)) {
            return false;
        }

        return filesize($left) === filesize($right)
            && hash_file('sha256', $left) === hash_file('sha256', $right);
    }

    private function children(string $directory): array
    {
        $items = scandir($directory);

        if ($items === false) {
            return [];
        }

        return array_values(array_filter($items, static fn(string $item): bool => $item !== '.' && $item !== '..'));
    }

    private function writeReport(string $status): void
    {
        $report = $this->path('Code-Review-And-ToDo/component-taxonomy/component-taxonomy-freeze-report.md');
        $directory = dirname($report);

        if (!is_dir($directory)) {
            mkdir($directory, 0o777, true);
        }

        $lines = [
            '# Component Taxonomy Freeze Report',
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
        $lines[] = '- DataFoundation/DataLayer bridge cleanup is not performed by this script.';
        $lines[] = '- Run composer dump-autoload after apply.';
        $lines[] = '- Run architecture checkers after apply.';

        file_put_contents($report, implode(PHP_EOL, $lines) . PHP_EOL);
    }

    private function printConflicts(): void
    {
        echo PHP_EOL;
        echo '❌ Conflicts found. Nothing was moved.' . PHP_EOL;

        foreach ($this->conflicts as $conflict) {
            echo ' - ' . $conflict . PHP_EOL;
        }

        echo PHP_EOL;
        echo 'Fix conflicts manually or inspect the report before applying.' . PHP_EOL;
    }

    private function moveDirectory(string $from, string $to, string $reason): void
    {
        if (!is_dir($from)) {
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

        if (!$this->apply) {
            return;
        }

        $this->mergeMove($from, $to);
    }

    private function mergeMove(string $from, string $to): void
    {
        if (is_file($from)) {
            $this->moveFile($from, $to);

            return;
        }

        if (!is_dir($from)) {
            return;
        }

        if (!is_dir($to)) {
            $parent = dirname($to);

            if (!is_dir($parent)) {
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

    private function moveFile(string $from, string $to): void
    {
        $parent = dirname($to);

        if (!is_dir($parent)) {
            mkdir($parent, 0o777, true);
        }

        if (is_file($to)) {
            if ($this->sameFile($from, $to)) {
                unlink($from);

                return;
            }

            throw new RuntimeException('Refusing to overwrite different file: ' . $this->relative($to));
        }

        rename($from, $to);
    }

    private function removeDirectoryIfEmpty(string $directory): void
    {
        if (is_dir($directory) && $this->children($directory) === []) {
            rmdir($directory);
        }
    }

    private function rewriteNamespaces(): void
    {
        $paths = [
            $this->path('components'),
            $this->path('framework'),
            $this->path('bin'),
            $this->path('config'),
        ];

        $changed = 0;

        foreach ($paths as $path) {
            if (!file_exists($path)) {
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

                $operation = 'REWRITE `' . $this->relative($file) . '`';
                $this->operations[] = $operation;

                echo ($this->apply ? '' : '[dry-run] ') . $operation . PHP_EOL;

                if ($this->apply) {
                    file_put_contents($file, $updated);
                }

                $changed++;
            }
        }

        echo PHP_EOL . ('Namespace rewrite candidates: ' . $changed) . PHP_EOL;
    }

    private function phpFiles(string $path): Generator
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
}

try {
    exit(new FreezeComponentTaxonomy($argv)->run());
} catch (Throwable $throwable) {
    fwrite(STDERR, 'ERROR: ' . $throwable->getMessage() . PHP_EOL);
    exit(1);
}
