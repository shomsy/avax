<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2).'/vendor/autoload.php';

final class StaticIntegrityRule
{
    /**
     * @param  Closure(string, string): RuleResult  $repair
     */
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly bool $fixable,
        private readonly Closure $repair,
    ) {
    }

    public function apply(string $path, string $content): RuleResult
    {
        return ($this->repair)($path, $content);
    }
}

final class RuleResult
{
    /**
     * @param  list<string>  $messages
     */
    public function __construct(
        public readonly string $content,
        public readonly array $messages,
    ) {
    }
}

final class FileReport
{
    /**
     * @param  list<string>  $messages
     */
    public function __construct(
        public readonly string $path,
        public readonly bool $changed,
        public readonly array $messages,
    ) {
    }
}

$root = dirname(__DIR__, 2);
$arguments = array_slice($argv, 1);

if (in_array('--help', $arguments, true) || in_array('-h', $arguments, true)) {
    printHelp();
    exit(0);
}

$rules = avaxStaticIntegrityRules();

if (in_array('--list-rules', $arguments, true)) {
    foreach ($rules as $rule) {
        $mode = $rule->fixable ? 'fixable' : 'check-only';
        echo "{$rule->name} ({$mode}) - {$rule->description}\n";
    }

    exit(0);
}

$apply = in_array('--apply', $arguments, true);
$selectedRuleNames = optionValues($arguments, '--rule=');
$selectedPaths = optionValues($arguments, '--path=');

if ($selectedPaths === []) {
    $selectedPaths = ['framework', 'components', 'routes', 'examples', 'tests'];
}

if ($selectedRuleNames !== []) {
    $rules = array_values(array_filter(
        $rules,
        static fn (StaticIntegrityRule $rule): bool => in_array($rule->name, $selectedRuleNames, true),
    ));

    $knownRuleNames = array_map(
        static fn (StaticIntegrityRule $rule): string => $rule->name,
        avaxStaticIntegrityRules(),
    );

    foreach ($selectedRuleNames as $selectedRuleName) {
        if (! in_array($selectedRuleName, $knownRuleNames, true)) {
            fwrite(STDERR, "Unknown rule: {$selectedRuleName}\n");
            exit(2);
        }
    }
}

$files = collectPhpFiles($root, $selectedPaths);
$reports = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    if ($content === false) {
        continue;
    }

    $relativePath = relativePath($root, $file);
    $updatedContent = $content;
    $messages = [];

    foreach ($rules as $rule) {
        $result = $rule->apply($relativePath, $updatedContent);
        if ($result->messages !== []) {
            foreach ($result->messages as $message) {
                $prefix = $rule->fixable ? 'fixable' : 'check-only';
                $messages[] = "{$rule->name} [{$prefix}]: {$message}";
            }
        }

        $updatedContent = $result->content;
    }

    $changed = $updatedContent !== $content;
    if ($changed && $apply) {
        file_put_contents($file, $updatedContent);
    }

    if ($changed || $messages !== []) {
        $reports[] = new FileReport(
            path: $relativePath,
            changed: $changed,
            messages: $messages,
        );
    }
}

$mode = $apply ? 'apply' : 'dry-run';
echo "AvaX static-integrity fixer ({$mode})\n";
echo 'Checked files: '.count($files)."\n";
echo 'Affected files: '.count($reports)."\n\n";

foreach ($reports as $report) {
    $status = $report->changed ? ($apply ? 'UPDATED' : 'WOULD UPDATE') : 'CHECK';
    echo "{$status} {$report->path}\n";
    foreach ($report->messages as $message) {
        echo "  - {$message}\n";
    }
}

if ($reports !== []) {
    exit(1);
}

exit(0);

function printHelp(): void
{
    echo <<<'HELP'
AvaX static-integrity fixer

Usage:
  php tooling/refactor/avax-static-integrity-fixer.php [--apply] [--path=DIR_OR_FILE] [--rule=RULE]
  php tooling/refactor/avax-static-integrity-fixer.php --list-rules

Default mode is dry-run. Use --apply only after reviewing the report.

HELP;
}

/**
 * @return list<StaticIntegrityRule>
 */
function avaxStaticIntegrityRules(): array
{
    return [
        new StaticIntegrityRule(
            name: 'nested-system-namespace',
            description: 'Collapse accidental System\\System namespace drift.',
            fixable: true,
            repair: static fn (string $path, string $content): RuleResult => replaceExact(
                content: $content,
                replacements: [
                    '\\System\\System\\' => '\\System\\',
                    '\\\\System\\\\System\\\\' => '\\\\System\\\\',
                ],
                message: 'collapsed nested System namespace segment',
            ),
        ),
        new StaticIntegrityRule(
            name: 'legacy-router-public-surface',
            description: 'Move legacy router imports to the canonical System\\PublicSurface namespace.',
            fixable: true,
            repair: static fn (string $path, string $content): RuleResult => replaceExact(
                content: $content,
                replacements: [
                    'Avax\\Components\\HTTP\\Router\\RouterInterface' => 'Avax\\Components\\HTTP\\Router\\System\\PublicSurface\\RouterInterface',
                    'Avax\\Components\\HTTP\\Router\\Router' => 'Avax\\Components\\HTTP\\Router\\System\\PublicSurface\\Router',
                ],
                message: 'rewrote legacy router public-surface namespace',
            ),
        ),
        new StaticIntegrityRule(
            name: 'route-builder-dsl-type',
            description: 'Rewrite stale RouteBuilder route DSL type hints to RouterInterface.',
            fixable: true,
            repair: static fn (string $path, string $content): RuleResult => replaceRouteBuilderDslType($content),
        ),
        new StaticIntegrityRule(
            name: 'container-provider-named-arguments',
            description: 'Rewrite stale container provider named arguments id/implementation to abstract/concrete.',
            fixable: true,
            repair: static fn (string $path, string $content): RuleResult => replaceContainerNamedArguments($content),
        ),
        new StaticIntegrityRule(
            name: 'explicit-null-union',
            description: 'Rewrite simple ?Type nullable declarations to Type|null.',
            fixable: true,
            repair: static fn (string $path, string $content): RuleResult => replaceShortNullableTypes($content),
        ),
        new StaticIntegrityRule(
            name: 'forbidden-technical-segments',
            description: 'Report forbidden generic architecture segments.',
            fixable: false,
            repair: static fn (string $path, string $content): RuleResult => detectForbiddenTechnicalSegments($path, $content),
        ),
    ];
}

/**
 * @param  array<string, string>  $replacements
 */
function replaceExact(string $content, array $replacements, string $message): RuleResult
{
    $updatedContent = str_replace(
        search: array_keys($replacements),
        replace: array_values($replacements),
        subject: $content,
    );

    if ($updatedContent === $content) {
        return new RuleResult(content: $content, messages: []);
    }

    return new RuleResult(content: $updatedContent, messages: [$message]);
}

function replaceRouteBuilderDslType(string $content): RuleResult
{
    $updatedContent = str_replace(
        search: 'use Avax\\Components\\HTTP\\Router\\System\\PublicSurface\\RouteBuilder;',
        replace: 'use Avax\\Components\\HTTP\\Router\\System\\PublicSurface\\RouterInterface;',
        subject: $content,
    );

    $updatedContent = (string) preg_replace(
        pattern: '/\bRouteBuilder(\s+\$[A-Za-z_][A-Za-z0-9_]*)/',
        replacement: 'RouterInterface$1',
        subject: $updatedContent,
    );

    if ($updatedContent === $content) {
        return new RuleResult(content: $content, messages: []);
    }

    return new RuleResult(
        content: $updatedContent,
        messages: ['rewrote RouteBuilder import/type hints to RouterInterface'],
    );
}

function replaceContainerNamedArguments(string $content): RuleResult
{
    $updatedContent = replaceNamedArgumentsInCalls(
        content: $content,
        methodNames: ['bind', 'singleton', 'scoped'],
        argumentMap: [
            'id' => 'abstract',
            'implementation' => 'concrete',
        ],
    );

    if ($updatedContent === $content) {
        return new RuleResult(content: $content, messages: []);
    }

    return new RuleResult(
        content: $updatedContent,
        messages: ['rewrote provider registration named arguments'],
    );
}

function replaceShortNullableTypes(string $content): RuleResult
{
    $updatedContent = (string) preg_replace(
        pattern: '/(?<![A-Za-z0-9_\\\\])\?([\\\\A-Za-z_][A-Za-z0-9_\\\\]*)(\s+\$[A-Za-z_][A-Za-z0-9_]*)/',
        replacement: '$1|null$2',
        subject: $content,
    );

    $updatedContent = (string) preg_replace(
        pattern: '/(:\s*)\?([\\\\A-Za-z_][A-Za-z0-9_\\\\]*)(\s*[{;=,])/',
        replacement: '$1$2|null$3',
        subject: $updatedContent,
    );

    if ($updatedContent === $content) {
        return new RuleResult(content: $content, messages: []);
    }

    return new RuleResult(
        content: $updatedContent,
        messages: ['rewrote short nullable declarations to explicit null unions'],
    );
}

function detectForbiddenTechnicalSegments(string $path, string $content): RuleResult
{
    $forbiddenSegments = ['Services', 'Helpers', 'Utils', 'Common', 'Shared', 'Managers', 'Core', 'Support'];
    $messages = [];
    $pathSegments = preg_split('/[\/\\\\]/', $path);

    if (is_array($pathSegments)) {
        foreach ($pathSegments as $pathSegment) {
            if (in_array($pathSegment, $forbiddenSegments, true)) {
                $messages[] = "forbidden path segment '{$pathSegment}'";
            }
        }
    }

    foreach ($forbiddenSegments as $forbiddenSegment) {
        if (preg_match('/namespace\s+[^;]*\\\\'.preg_quote($forbiddenSegment, '/').'(?:\\\\|;)/', $content) === 1) {
            $messages[] = "forbidden namespace segment '{$forbiddenSegment}'";
        }
    }

    return new RuleResult(
        content: $content,
        messages: array_values(array_unique($messages)),
    );
}

/**
 * @param  list<string>  $methodNames
 * @param  array<string, string>  $argumentMap
 */
function replaceNamedArgumentsInCalls(string $content, array $methodNames, array $argumentMap): string
{
    $offset = 0;
    $updatedContent = $content;
    $methodPattern = implode('|', array_map(
        static fn (string $methodName): string => preg_quote($methodName, '/'),
        $methodNames,
    ));

    while (preg_match('/->\s*(?:'.$methodPattern.')\s*\(/', $updatedContent, $matches, PREG_OFFSET_CAPTURE, $offset) === 1) {
        $matchText = $matches[0][0];
        $matchOffset = $matches[0][1];
        $openParenthesis = $matchOffset + strlen($matchText) - 1;
        $closeParenthesis = findClosingParenthesis($updatedContent, $openParenthesis);

        if ($closeParenthesis === null) {
            $offset = $openParenthesis + 1;

            continue;
        }

        $call = substr($updatedContent, $matchOffset, $closeParenthesis - $matchOffset + 1);
        $updatedCall = $call;

        foreach ($argumentMap as $oldName => $newName) {
            $updatedCall = (string) preg_replace(
                pattern: '/(?<![A-Za-z0-9_])'.preg_quote($oldName, '/').'\s*:/',
                replacement: $newName.':',
                subject: $updatedCall,
            );
        }

        if ($updatedCall !== $call) {
            $updatedContent = substr_replace(
                string: $updatedContent,
                replace: $updatedCall,
                offset: $matchOffset,
                length: strlen($call),
            );

            $offset = $matchOffset + strlen($updatedCall);

            continue;
        }

        $offset = $closeParenthesis + 1;
    }

    return $updatedContent;
}

function findClosingParenthesis(string $content, int $openParenthesis) : int|null
{
    $depth = 0;
    $length = strlen($content);
    $quote = '';
    $escaped = false;

    for ($index = $openParenthesis; $index < $length; $index++) {
        $char = $content[$index];

        if ($quote !== '') {
            if ($escaped) {
                $escaped = false;

                continue;
            }

            if ($char === '\\') {
                $escaped = true;

                continue;
            }

            if ($char === $quote) {
                $quote = '';
            }

            continue;
        }

        if ($char === '\'' || $char === '"') {
            $quote = $char;

            continue;
        }

        if ($char === '(') {
            $depth++;

            continue;
        }

        if ($char === ')') {
            $depth--;
            if ($depth === 0) {
                return $index;
            }
        }
    }

    return null;
}

/**
 * @param  list<string>  $arguments
 * @return list<string>
 */
function optionValues(array $arguments, string $prefix): array
{
    $values = [];

    foreach ($arguments as $argument) {
        if (str_starts_with($argument, $prefix)) {
            $values[] = substr($argument, strlen($prefix));
        }
    }

    return array_values(array_filter(
        $values,
        static fn (string $value): bool => $value !== '',
    ));
}

/**
 * @param  list<string>  $paths
 * @return list<string>
 */
function collectPhpFiles(string $root, array $paths): array
{
    $files = [];

    foreach ($paths as $path) {
        $absolutePath = absolutePath($root, $path);

        if (! file_exists($absolutePath)) {
            fwrite(STDERR, "Path does not exist: {$path}\n");
            exit(2);
        }

        if (is_file($absolutePath)) {
            if (str_ends_with($absolutePath, '.php')) {
                $files[] = $absolutePath;
            }

            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            iterator: new RecursiveDirectoryIterator(
                directory: $absolutePath,
                flags: RecursiveDirectoryIterator::SKIP_DOTS,
            ),
        );

        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo) {
                continue;
            }

            if (! $file->isFile()) {
                continue;
            }

            if ($file->getExtension() !== 'php') {
                continue;
            }

            $pathName = $file->getPathname();
            if (pathShouldBeSkipped(relativePath($root, $pathName))) {
                continue;
            }

            $files[] = $pathName;
        }
    }

    sort($files);

    return array_values(array_unique($files));
}

function absolutePath(string $root, string $path): string
{
    if (str_starts_with($path, '/')) {
        return $path;
    }

    return $root.'/'.ltrim($path, '/');
}

function relativePath(string $root, string $path): string
{
    $normalizedRoot = rtrim($root, '/').'/';

    if (str_starts_with($path, $normalizedRoot)) {
        return substr($path, strlen($normalizedRoot));
    }

    return $path;
}

function pathShouldBeSkipped(string $path): bool
{
    $skipped = [
        '/vendor/',
        '/EVIDENCE/',
        '/build/',
        '/.git/',
        '/.phpunit.cache/',
        '/node_modules/',
    ];

    $normalized = '/'.trim($path, '/').'/';

    foreach ($skipped as $skip) {
        if (str_contains($normalized, $skip)) {
            return true;
        }
    }

    return false;
}
