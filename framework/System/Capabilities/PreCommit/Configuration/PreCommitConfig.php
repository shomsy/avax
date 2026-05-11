<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Configuration;

/**
 * PreCommit Configuration
 *
 * Central configuration for PreCommit discipline system.
 */
final class PreCommitConfig
{
    /** @var list<string> */
    private array $enabledChecks;

    /** @var array<string, string> */
    private array $severityOverrides = [];

    /** @var list<string> */
    private readonly array $blockingChecks;

    /** @var list<string> */
    private readonly array $warningChecks;

    private readonly string $reportPath;

    private readonly string $todoPath;

    private readonly string $toolingPath;

    private bool $dryRun = true;

    private bool $autoFix = false;

    private bool $installHook = false;

    /**
     * @param  list<string>|null  $enabledChecks
     * @param  list<string>|null  $blockingChecks
     * @param  list<string>|null  $warningChecks
     */
    public function __construct(array|null $enabledChecks = null, array|null $blockingChecks = null, array|null $warningChecks = null, string|null $reportPath = null, string|null $todoPath = null, string|null $toolingPath = null
    ) {
        $basePath = getcwd() ?: '.';

        $this->enabledChecks = $enabledChecks ?? [
            'CheckNamingConventions',
            'CheckHowToRules',
            'CheckForbiddenWords',
            'CheckPublicSurfaceRules',
            'CheckPhpSyntax',
            'CheckFileStructure',
            'DetectLegacyCode',
            'DetectDeprecatedCode',
            'DetectLegacyAliases',
            'DetectTodoComments',
            'DetectToolingScripts',
            'DetectArchitectureViolations',
            'RunExternalToolingScripts',
        ];

        $this->blockingChecks = $blockingChecks ?? [
            'CheckPhpSyntax',
            'CheckNamingConventions',
            'CheckHowToRules',
            'CheckForbiddenWords',
        ];

        $this->warningChecks = $warningChecks ?? [
            'DetectLegacyCode',
            'DetectDeprecatedCode',
            'DetectLegacyAliases',
            'DetectTodoComments',
        ];

        $this->reportPath = $reportPath ?? $basePath . '/EVIDENCE/pre-commit';
        $this->todoPath   = $todoPath ?? $basePath . '/EVIDENCE/pre-commit/pre-commit-todo.md';
        $this->toolingPath = $toolingPath ?? $basePath.'/tooling';
    }

    public function isCheckEnabled(string $checkName): bool
    {
        return in_array($checkName, $this->enabledChecks, true);
    }

    public function getSeverity(string $checkName): string
    {
        return $this->severityOverrides[$checkName] ?? match (true) {
            $this->isBlocking($checkName) => 'error',
            $this->isWarning($checkName) => 'warning',
            default => 'info',
        };
    }

    public function isBlocking(string $checkName): bool
    {
        return in_array($checkName, $this->blockingChecks, true);
    }

    public function isWarning(string $checkName): bool
    {
        return in_array($checkName, $this->warningChecks, true);
    }

    public function getReportPath(): string
    {
        return $this->reportPath;
    }

    public function getTodoPath(): string
    {
        return $this->todoPath;
    }

    public function getToolingPath(): string
    {
        return $this->toolingPath;
    }

    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    public function setDryRun(bool $dryRun): self
    {
        $this->dryRun = $dryRun;

        return $this;
    }

    public function isAutoFixEnabled(): bool
    {
        return $this->autoFix;
    }

    public function shouldInstallHook(): bool
    {
        return $this->installHook;
    }

    public function setAutoFix(bool $autoFix): self
    {
        $this->autoFix = $autoFix;

        return $this;
    }

    public function setInstallHook(bool $installHook): self
    {
        $this->installHook = $installHook;

        return $this;
    }

    public function enableCheck(string $checkName): self
    {
        if (! in_array($checkName, $this->enabledChecks)) {
            $this->enabledChecks[] = $checkName;
        }

        return $this;
    }

    public function disableCheck(string $checkName): self
    {
        $this->enabledChecks = array_values(array_filter(
            $this->enabledChecks,
            static fn (string $check): bool => $check !== $checkName
        ));

        return $this;
    }

    /** @return list<string> */
    public function getEnabledChecks(): array
    {
        return $this->enabledChecks;
    }

    /** @return list<string> */
    public function getBlockingChecks(): array
    {
        return $this->blockingChecks;
    }

    /** @return list<string> */
    public function getWarningChecks(): array
    {
        return $this->warningChecks;
    }
}
