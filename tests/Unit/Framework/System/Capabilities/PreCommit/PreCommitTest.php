<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Capabilities\PreCommit;

use Avax\Framework\System\Capabilities\PreCommit\Configuration\PreCommitConfig;
use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitIssue;
use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitResult;
use Avax\Framework\System\Capabilities\PreCommit\PreCommit;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for PreCommit models and configuration.
 * Integration tests that actually run PreCommit checks are in PreCommitIntegrationTest.
 */
final class PreCommitTest extends TestCase
{
    public function test_it_blocks_on_critical_issues(): void
    {
        $result = new PreCommitResult();

        $result->addIssue(new PreCommitIssue(
            checkName: 'Test',
            severity: PreCommitIssue::SEVERITY_CRITICAL,
            message: 'Critical failure'
        ));

        self::assertFalse($result->canCommit());
        self::assertTrue($result->isBlocked());
    }

    public function test_it_blocks_on_error_issues(): void
    {
        $result = new PreCommitResult();

        $result->addIssue(new PreCommitIssue(
            checkName: 'Test',
            severity: PreCommitIssue::SEVERITY_ERROR,
            message: 'Error failure'
        ));

        self::assertFalse($result->canCommit());
        self::assertTrue($result->isBlocked());
    }

    public function test_it_allows_commit_with_only_warnings(): void
    {
        $result = new PreCommitResult();

        $result->addIssue(new PreCommitIssue(
            checkName: 'Test',
            severity: PreCommitIssue::SEVERITY_WARNING,
            message: 'Warning only'
        ));

        self::assertTrue($result->canCommit());
        self::assertFalse($result->isBlocked());
    }

    public function test_it_allows_commit_with_only_info(): void
    {
        $result = new PreCommitResult();

        $result->addIssue(new PreCommitIssue(
            checkName: 'Test',
            severity: PreCommitIssue::SEVERITY_INFO,
            message: 'Info only'
        ));

        self::assertTrue($result->canCommit());
    }

    public function test_it_starts_passed_with_no_issues(): void
    {
        $result = new PreCommitResult();

        self::assertSame(PreCommitResult::STATUS_PASSED, $result->determineStatus());
        self::assertTrue($result->isPassed());
        self::assertTrue($result->canCommit());
    }

    public function test_it_can_disable_specific_checks(): void
    {
        $config = new PreCommitConfig();
        $config->disableCheck('CheckPhpSyntax');
        $config->disableCheck('CheckNamingConventions');

        self::assertFalse($config->isCheckEnabled('CheckPhpSyntax'));
        self::assertFalse($config->isCheckEnabled('CheckNamingConventions'));
    }

    public function test_it_can_enable_specific_checks(): void
    {
        $config = new PreCommitConfig();
        $config->disableCheck('CheckPhpSyntax');

        self::assertFalse($config->isCheckEnabled('CheckPhpSyntax'));

        $config->enableCheck('CheckPhpSyntax');

        self::assertTrue($config->isCheckEnabled('CheckPhpSyntax'));
    }

    public function test_it_correctly_identifies_blocking_checks(): void
    {
        $config = new PreCommitConfig();

        self::assertTrue($config->isBlocking('CheckPhpSyntax'));
        self::assertTrue($config->isBlocking('CheckNamingConventions'));
        self::assertTrue($config->isBlocking('CheckHowToRules'));
        self::assertTrue($config->isBlocking('CheckForbiddenWords'));
        self::assertFalse($config->isBlocking('DetectLegacyCode'));
    }

    public function test_it_correctly_identifies_warning_checks(): void
    {
        $config = new PreCommitConfig();

        self::assertTrue($config->isWarning('DetectLegacyCode'));
        self::assertTrue($config->isWarning('DetectDeprecatedCode'));
        self::assertTrue($config->isWarning('DetectLegacyAliases'));
        self::assertTrue($config->isWarning('DetectTodoComments'));
        self::assertFalse($config->isWarning('CheckPhpSyntax'));
    }

    public function test_it_returns_correct_severity_for_each_check_type(): void
    {
        $config = new PreCommitConfig();

        self::assertSame('error', $config->getSeverity('CheckPhpSyntax'));
        self::assertSame('error', $config->getSeverity('CheckNamingConventions'));
        self::assertSame('warning', $config->getSeverity('DetectLegacyCode'));
        self::assertSame('info', $config->getSeverity('DetectToolingScripts'));
    }

    public function test_it_filters_issues_by_severity(): void
    {
        $result = new PreCommitResult();

        $result->addIssue(new PreCommitIssue('C1', PreCommitIssue::SEVERITY_CRITICAL, 'C'));
        $result->addIssue(new PreCommitIssue('E1', PreCommitIssue::SEVERITY_ERROR, 'E'));
        $result->addIssue(new PreCommitIssue('E2', PreCommitIssue::SEVERITY_ERROR, 'E2'));
        $result->addIssue(new PreCommitIssue('W1', PreCommitIssue::SEVERITY_WARNING, 'W'));
        $result->addIssue(new PreCommitIssue('I1', PreCommitIssue::SEVERITY_INFO, 'I'));

        self::assertCount(1, $result->getCriticalIssues());
        self::assertCount(2, $result->getErrorIssues());
        self::assertCount(1, $result->getWarningIssues());
        self::assertCount(1, $result->getInfoIssues());
        self::assertSame(5, $result->getTotalIssueCount());
    }

    public function test_it_generates_delete_candidates(): void
    {
        $result = new PreCommitResult();

        $result->addIssue(new PreCommitIssue(
            'Detect',
            PreCommitIssue::SEVERITY_WARNING,
            'Dead code',
            deleteClassification: PreCommitIssue::DELETE_SAFE
        ));

        $result->addIssue(new PreCommitIssue(
            'Detect',
            PreCommitIssue::SEVERITY_WARNING,
            'Unknown',
            deleteClassification: PreCommitIssue::DELETE_UNSAFE
        ));

        self::assertCount(1, $result->getDeleteCandidates());
    }

    public function test_it_generates_todo_lines(): void
    {
        $result = new PreCommitResult();
        $result->addIssue(new PreCommitIssue(
            'CheckPhpSyntax',
            PreCommitIssue::SEVERITY_ERROR,
            'Syntax error',
            'test.php',
            10,
            'PHP_SYNTAX'
        ));

        $lines = $result->getTodoLines();

        self::assertCount(1, $lines);
        self::assertStringContainsString('[ERROR]', $lines[0]);
        self::assertStringContainsString('CheckPhpSyntax', $lines[0]);
    }

    public function test_it_converts_to_array_correctly(): void
    {
        $result = new PreCommitResult();
        $result->addIssue(new PreCommitIssue(
            'Check',
            PreCommitIssue::SEVERITY_ERROR,
            'Error',
            'file.php',
            1,
            'CODE'
        ));
        $result->addPassedCheck('CheckPhpSyntax');

        $array = $result->toArray();

        self::assertArrayHasKey('status', $array);
        self::assertArrayHasKey('summary', $array);
        self::assertArrayHasKey('issues', $array);
        self::assertArrayHasKey('passed_checks', $array);
        self::assertArrayHasKey('execution_time_seconds', $array);

        self::assertSame(1, $array['summary']['errors']);
        self::assertSame(1, $array['summary']['passed_checks']);
    }

    public function test_it_tracks_passed_checks(): void
    {
        $result = new PreCommitResult();
        $result->addPassedCheck('CheckPhpSyntax');
        $result->addPassedCheck('CheckNamingConventions');

        self::assertCount(2, $result->getPassedChecks());
        self::assertContains('CheckPhpSyntax', $result->getPassedChecks());
        self::assertContains('CheckNamingConventions', $result->getPassedChecks());
    }

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

    public function test_it_sets_dry_run_mode(): void
    {
        $config = new PreCommitConfig();

        self::assertTrue($config->isDryRun());

        $config->setDryRun(false);
        self::assertFalse($config->isDryRun());

        $config->setDryRun(true);
        self::assertTrue($config->isDryRun());
    }

    public function test_it_returns_paths(): void
    {
        $config = new PreCommitConfig();

        self::assertStringContainsString('Code-Review-And-ToDo/pre-commit', $config->getReportPath());
        self::assertStringContainsString('pre-commit-todo.md', $config->getTodoPath());
        self::assertStringContainsString('tooling', $config->getToolingPath());
    }

    public function test_precommit_result_provides_access_to_sub_components(): void
    {
        $config = new PreCommitConfig();
        $result = new PreCommitResult();

        $result->addMetadata('key', 'value');

        self::assertSame('value', $result->getMetadata()['key']);
    }
}
