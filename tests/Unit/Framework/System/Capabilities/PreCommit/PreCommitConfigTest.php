<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Capabilities\PreCommit;

use Avax\Framework\System\Capabilities\PreCommit\Configuration\PreCommitConfig;
use PHPUnit\Framework\TestCase;

final class PreCommitConfigTest extends TestCase
{
    public function test_it_has_default_enabled_checks(): void
    {
        $config = new PreCommitConfig();
        $checks = $config->getEnabledChecks();

        self::assertContains('CheckPhpSyntax', $checks);
        self::assertContains('CheckNamingConventions', $checks);
        self::assertContains('CheckHowToRules', $checks);
        self::assertContains('CheckForbiddenWords', $checks);
        self::assertContains('DetectLegacyCode', $checks);
        self::assertContains('DetectArchitectureViolations', $checks);
    }

    public function test_it_has_default_blocking_checks(): void
    {
        $config = new PreCommitConfig();
        $blocking = $config->getBlockingChecks();

        self::assertContains('CheckPhpSyntax', $blocking);
        self::assertContains('CheckNamingConventions', $blocking);
        self::assertContains('CheckHowToRules', $blocking);
        self::assertContains('CheckForbiddenWords', $blocking);
    }

    public function test_it_identifies_blocking_checks(): void
    {
        $config = new PreCommitConfig();

        self::assertTrue($config->isBlocking('CheckPhpSyntax'));
        self::assertTrue($config->isBlocking('CheckNamingConventions'));
        self::assertFalse($config->isBlocking('DetectLegacyCode'));
    }

    public function test_it_identifies_warning_checks(): void
    {
        $config = new PreCommitConfig();

        self::assertTrue($config->isWarning('DetectLegacyCode'));
        self::assertTrue($config->isWarning('DetectDeprecatedCode'));
        self::assertFalse($config->isWarning('CheckPhpSyntax'));
    }

    public function test_it_enables_and_disables_checks(): void
    {
        $config = new PreCommitConfig();

        self::assertTrue($config->isCheckEnabled('CheckPhpSyntax'));

        $config->disableCheck('CheckPhpSyntax');
        self::assertFalse($config->isCheckEnabled('CheckPhpSyntax'));

        $config->enableCheck('CheckPhpSyntax');
        self::assertTrue($config->isCheckEnabled('CheckPhpSyntax'));
    }

    public function test_it_sets_dry_run_mode(): void
    {
        $config = new PreCommitConfig();

        self::assertTrue($config->isDryRun());

        $config->setDryRun(false);
        self::assertFalse($config->isDryRun());

        $config->setDryRun(true);
        self::assertTrue($config->isDryRun());
    }

    public function test_it_returns_severity_for_check(): void
    {
        $config = new PreCommitConfig();

        self::assertSame('error', $config->getSeverity('CheckPhpSyntax'));
        self::assertSame('warning', $config->getSeverity('DetectLegacyCode'));
        self::assertSame('info', $config->getSeverity('DetectToolingScripts'));
    }

    public function test_it_returns_paths(): void
    {
        $config = new PreCommitConfig();

        self::assertStringContainsString('Code-Review-And-ToDo/pre-commit', $config->getReportPath());
        self::assertStringContainsString('pre-commit-todo.md', $config->getTodoPath());
        self::assertStringContainsString('tooling', $config->getToolingPath());
    }
}
