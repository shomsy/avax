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
    /** @var array<string> */
    private array $enabledChecks;

    /** @var array<string, string> */
    private array $severityOverrides;

    /** @var array<string> */
    private array $blockingChecks;

    /** @var array<string> */
    private array $warningChecks;

    private string $reportPath;
    private string $todoPath;
    private string $toolingPath;
    private bool   $dryRun;
    private bool   $autoFix;
    private bool   $installHook;

    public function __construct(
        ?array  $enabledChecks = null,
        ?array  $blockingChecks = null,
        ?array  $warningChecks = null,
        ?string $reportPath = null,
        ?string $todoPath = null,
        ?string $toolingPath = null
    )
    {
        $basePath = getcwd();

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

        $this->severityOverrides = [];

        $this->reportPath  = $reportPath ?? $basePath . '/Code-Review-And-ToDo/pre-commit';
        $this->todoPath    = $todoPath ?? $basePath . '/Code-Review-And-ToDo/pre-commit/pre-commit-todo.md';
        $this->toolingPath = $toolingPath ?? $basePath . '/tooling';

        $this->dryRun      = true;
        $this->autoFix     = false;
        $this->installHook = false;
    }

    public function isCheckEnabled(string $checkName) : bool
    {
        return in_array($checkName, $this->enabledChecks, true);
    }

    public function getSeverity(string $checkName) : string
    {
        return $this->severityOverrides[$checkName] ?? match (true) {
            $this->isBlocking($checkName) => 'error',
            $this->isWarning($checkName)  => 'warning',
            default                       => 'info',
        };
    }

    public function isBlocking(string $checkName) : bool
    {
        return in_array($checkName, $this->blockingChecks, true);
    }

    public function isWarning(string $checkName) : bool
    {
        return in_array($checkName, $this->warningChecks, true);
    }

    public function getReportPath() : string
    {
        return $this->reportPath;
    }

    public function getTodoPath() : string
    {
        return $this->todoPath;
    }

    public function getToolingPath() : string
    {
        return $this->toolingPath;
    }

    public function isDryRun() : bool
    {
        return $this->dryRun;
    }

    public function setDryRun(bool $dryRun) : self
    {
        $this->dryRun = $dryRun;

        return $this;
    }

    public function isAutoFixEnabled() : bool
    {
        return $this->autoFix;
    }

    public function shouldInstallHook() : bool
    {
        return $this->installHook;
    }

    public function setAutoFix(bool $autoFix) : self
    {
        $this->autoFix = $autoFix;

        return $this;
    }

    public function setInstallHook(bool $installHook) : self
    {
        $this->installHook = $installHook;

        return $this;
    }

    public function enableCheck(string $checkName) : self
    {
        if (! in_array($checkName, $this->enabledChecks)) {
            $this->enabledChecks[] = $checkName;
        }

        return $this;
    }

    public function disableCheck(string $checkName) : self
    {
        $this->enabledChecks = array_filter(
            $this->enabledChecks,
            fn ($check) => $check !== $checkName
        );

        return $this;
    }

    /** @return array<string> */
    public function getEnabledChecks() : array
    {
        return $this->enabledChecks;
    }

    /** @return array<string> */
    public function getBlockingChecks() : array
    {
        return $this->blockingChecks;
    }

    /** @return array<string> */
    public function getWarningChecks() : array
    {
        return $this->warningChecks;
    }
}
