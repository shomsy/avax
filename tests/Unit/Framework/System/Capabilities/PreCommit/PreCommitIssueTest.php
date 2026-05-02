<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Capabilities\PreCommit;

use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitIssue;
use PHPUnit\Framework\TestCase;

final class PreCommitIssueTest extends TestCase
{
    public function test_it_creates_issue_with_required_fields(): void
    {
        $issue = new PreCommitIssue(
            checkName : 'CheckPhpSyntax',
            severity  : PreCommitIssue::SEVERITY_ERROR,
            message   : 'PHP syntax error',
            file      : 'src/Test.php',
            line      : 10,
            ruleCode  : 'PHP_SYNTAX'
        );

        self::assertSame('CheckPhpSyntax', $issue->getCheckName());
        self::assertSame(PreCommitIssue::SEVERITY_ERROR, $issue->getSeverity());
        self::assertSame('PHP syntax error', $issue->getMessage());
        self::assertSame('src/Test.php', $issue->getFile());
        self::assertSame(10, $issue->getLine());
        self::assertSame('PHP_SYNTAX', $issue->getRuleCode());
    }

    public function test_it_defaults_delete_classification_to_unsafe(): void
    {
        $issue = new PreCommitIssue(
            checkName : 'Test',
            severity  : PreCommitIssue::SEVERITY_ERROR,
            message   : 'Test message'
        );

        self::assertSame(PreCommitIssue::DELETE_UNSAFE, $issue->getDeleteClassification());
    }

    public function test_it_detects_blocking_severities(): void
    {
        $critical = new PreCommitIssue('Test', PreCommitIssue::SEVERITY_CRITICAL, 'Critical');
        $error    = new PreCommitIssue('Test', PreCommitIssue::SEVERITY_ERROR, 'Error');
        $warning  = new PreCommitIssue('Test', PreCommitIssue::SEVERITY_WARNING, 'Warning');
        $info     = new PreCommitIssue('Test', PreCommitIssue::SEVERITY_INFO, 'Info');

        self::assertTrue($critical->isBlocking());
        self::assertTrue($error->isBlocking());
        self::assertFalse($warning->isBlocking());
        self::assertFalse($info->isBlocking());
    }

    public function test_it_generates_location_string(): void
    {
        $issueWithLine = new PreCommitIssue('Test', 'error', 'Msg', 'file.php', 42);
        $issueWithoutLine = new PreCommitIssue('Test', 'error', 'Msg', 'file.php');

        self::assertSame('file.php:42', $issueWithLine->getLocation());
        self::assertSame('file.php', $issueWithoutLine->getLocation());
        self::assertSame('unknown', (new PreCommitIssue('Test', 'error', 'Msg'))->getLocation());
    }

    public function test_it_converts_to_array(): void
    {
        $issue = new PreCommitIssue(
            checkName : 'CheckPhpSyntax',
            severity  : PreCommitIssue::SEVERITY_ERROR,
            message   : 'Syntax error in file',
            file      : 'src/Test.php',
            line      : 5,
            ruleCode  : 'PHP_SYNTAX'
        );

        $array = $issue->toArray();

        self::assertSame('CheckPhpSyntax', $array['check_name']);
        self::assertSame('error', $array['severity']);
        self::assertSame('Syntax error in file', $array['message']);
        self::assertSame('src/Test.php', $array['file']);
        self::assertSame(5, $array['line']);
        self::assertSame('PHP_SYNTAX', $array['rule_code']);
        self::assertSame('src/Test.php:5', $array['location']);
    }

    public function test_it_generates_todo_line(): void
    {
        $issue = new PreCommitIssue(
            checkName : 'CheckPhpSyntax',
            severity  : PreCommitIssue::SEVERITY_ERROR,
            message   : 'PHP syntax error',
            file      : 'src/Test.php',
            line      : 10,
            ruleCode  : 'PHP_SYNTAX'
        );

        $todoLine = $issue->toTodoLine();

        self::assertStringContainsString('[ERROR]', $todoLine);
        self::assertStringContainsString('CheckPhpSyntax', $todoLine);
        self::assertStringContainsString('PHP syntax error', $todoLine);
        self::assertStringContainsString('src/Test.php:10', $todoLine);
    }

    public function test_it_creates_from_array(): void
    {
        $data = [
            'check_name' => 'CheckPhpSyntax',
            'severity'   => 'error',
            'message'    => 'Syntax error',
            'file'       => 'src/Test.php',
            'line'       => 15,
            'rule_code'  => 'PHP_SYNTAX',
        ];

        $issue = PreCommitIssue::fromArray($data);

        self::assertSame('CheckPhpSyntax', $issue->getCheckName());
        self::assertSame('error', $issue->getSeverity());
        self::assertSame('Syntax error', $issue->getMessage());
        self::assertSame('src/Test.php', $issue->getFile());
        self::assertSame(15, $issue->getLine());
        self::assertSame('PHP_SYNTAX', $issue->getRuleCode());
    }

    public function test_it_clones_with_metadata(): void
    {
        $issue = new PreCommitIssue('Test', 'error', 'Test message');
        $withMeta = $issue->withMetadata(['auto_fixable' => true]);

        self::assertNotSame($issue, $withMeta);
        self::assertTrue($withMeta->canAutoFix());
        self::assertFalse($issue->canAutoFix());
    }

    public function test_it_clones_with_delete_classification(): void
    {
        $issue = new PreCommitIssue('Test', 'warning', 'Test message');
        $safe = $issue->withDeleteClassification(PreCommitIssue::DELETE_SAFE);

        self::assertNotSame($issue, $safe);
        self::assertTrue($safe->canDelete());
        self::assertFalse($issue->canDelete());
    }
}
