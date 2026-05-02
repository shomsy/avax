<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Capabilities\PreCommit;

use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitIssue;
use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitResult;
use PHPUnit\Framework\TestCase;

final class PreCommitResultTest extends TestCase
{
    public function test_it_starts_as_passed(): void
    {
        $result = new PreCommitResult();

        self::assertSame(PreCommitResult::STATUS_PASSED, $result->determineStatus());
        self::assertTrue($result->isPassed());
        self::assertTrue($result->canCommit());
    }

    public function test_it_blocks_on_critical_issues(): void
    {
        $result = new PreCommitResult();
        $result->addIssue(new PreCommitIssue(
            checkName : 'Check',
            severity  : PreCommitIssue::SEVERITY_CRITICAL,
            message   : 'Critical issue'
        ));

        self::assertSame(PreCommitResult::STATUS_BLOCKED, $result->determineStatus());
        self::assertFalse($result->canCommit());
        self::assertTrue($result->isBlocked());
    }

    public function test_it_blocks_on_error_issues(): void
    {
        $result = new PreCommitResult();
        $result->addIssue(new PreCommitIssue(
            checkName : 'Check',
            severity  : PreCommitIssue::SEVERITY_ERROR,
            message   : 'Error issue'
        ));

        self::assertSame(PreCommitResult::STATUS_BLOCKED, $result->determineStatus());
        self::assertFalse($result->canCommit());
    }

    public function test_it_warns_on_warning_issues(): void
    {
        $result = new PreCommitResult();
        $result->addIssue(new PreCommitIssue(
            checkName : 'Check',
            severity  : PreCommitIssue::SEVERITY_WARNING,
            message   : 'Warning issue'
        ));

        self::assertSame(PreCommitResult::STATUS_WARNING, $result->determineStatus());
        self::assertTrue($result->canCommit());
    }

    public function test_it_tracks_passed_checks(): void
    {
        $result = new PreCommitResult();
        $result->addPassedCheck('CheckPhpSyntax');
        $result->addPassedCheck('CheckNamingConventions');

        self::assertCount(2, $result->getPassedChecks());
        self::assertContains('CheckPhpSyntax', $result->getPassedChecks());
    }

    public function test_it_filters_issues_by_severity(): void
    {
        $result = new PreCommitResult();
        $result->addIssue(new PreCommitIssue('C1', PreCommitIssue::SEVERITY_CRITICAL, 'C'));
        $result->addIssue(new PreCommitIssue('E1', PreCommitIssue::SEVERITY_ERROR, 'E'));
        $result->addIssue(new PreCommitIssue('W1', PreCommitIssue::SEVERITY_WARNING, 'W'));
        $result->addIssue(new PreCommitIssue('I1', PreCommitIssue::SEVERITY_INFO, 'I'));

        self::assertCount(1, $result->getCriticalIssues());
        self::assertCount(1, $result->getErrorIssues());
        self::assertCount(1, $result->getWarningIssues());
        self::assertCount(1, $result->getInfoIssues());
        self::assertSame(4, $result->getTotalIssueCount());
    }

    public function test_it_generates_summary_text(): void
    {
        $result = new PreCommitResult();
        $result->addIssue(new PreCommitIssue('Check', PreCommitIssue::SEVERITY_ERROR, 'Error msg', 'file.php', 10));
        $result->addPassedCheck('CheckPhpSyntax');

        $summary = $result->getSummaryText();

        self::assertStringContainsString('BLOCKED', $summary);
        self::assertStringContainsString('Errors', $summary);
        self::assertStringContainsString('Error msg', $summary);
        self::assertStringContainsString('file.php:10', $summary);
    }

    public function test_it_converts_to_array(): void
    {
        $result = new PreCommitResult();
        $result->addIssue(new PreCommitIssue('Check', PreCommitIssue::SEVERITY_ERROR, 'Error', 'f.php', 1, 'CODE'));
        $result->addPassedCheck('CheckPhpSyntax');

        $array = $result->toArray();

        self::assertArrayHasKey('status', $array);
        self::assertArrayHasKey('summary', $array);
        self::assertArrayHasKey('issues', $array);
        self::assertSame(1, $array['summary']['errors']);
        self::assertSame(1, $array['summary']['passed_checks']);
    }

    public function test_it_identifies_delete_candidates(): void
    {
        $result = new PreCommitResult();
        $result->addIssue(new PreCommitIssue(
            'Detect',
            PreCommitIssue::SEVERITY_WARNING,
            'Dead code',
            deleteClassification : PreCommitIssue::DELETE_SAFE
        ));
        $result->addIssue(new PreCommitIssue(
            'Detect',
            PreCommitIssue::SEVERITY_WARNING,
            'Alive code'
        ));

        self::assertCount(1, $result->getDeleteCandidates());
    }

    public function test_it_tracks_execution_time(): void
    {
        $result = new PreCommitResult();
        $result->setExecutionTime(1.5);

        self::assertSame(1.5, $result->getExecutionTime());
    }

    public function test_it_generates_todo_lines(): void
    {
        $result = new PreCommitResult();
        $result->addIssue(new PreCommitIssue('Check', PreCommitIssue::SEVERITY_ERROR, 'Error', 'f.php', 1, 'CODE'));

        $lines = $result->getTodoLines();

        self::assertCount(1, $lines);
        self::assertStringContainsString('[ERROR]', $lines[0]);
    }
}
