<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Foundation\DataHandling\DataTransfer\Capabilities\ErrorReporting;

use Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferFailure;
use Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferViolation;
use Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferViolations;
use PHPUnit\Framework\TestCase;

final class DataTransferViolationsTest extends TestCase
{
    public function test_it_returns_empty_collection_when_no_violations_are_recorded() : void
    {
        // Act
        $violations = DataTransferViolations::empty();

        // Assert
        self::assertCount(expectedCount: 0, haystack: $violations);
        self::assertSame(expected: [], actual: $violations->all());
        self::assertSame(expected: [], actual: $violations->toLegacyErrors());
    }

    public function test_it_groups_messages_by_path_when_multiple_violations_share_a_path() : void
    {
        // Arrange
        $violations = DataTransferViolations::from(violations: [
                                                                   new DataTransferViolation(path: 'email', code: 'required', message: 'Email is required.'),
                                                                   new DataTransferViolation(path: 'email', code: 'invalid', message: 'Email is invalid.'),
                                                                   new DataTransferViolation(path: 'name', code: 'required', message: 'Name is required.'),
                                                               ]);

        // Act
        $grouped = $violations->byPath();

        // Assert
        self::assertSame(
            expected: [
                          'email' => ['Email is required.', 'Email is invalid.'],
                          'name'  => ['Name is required.'],
                      ],
            actual  : $grouped,
        );
    }

    public function test_it_keeps_latest_legacy_error_when_multiple_violations_share_a_path() : void
    {
        // Arrange
        $violations = DataTransferViolations::from(violations: [
                                                                   new DataTransferViolation(path: 'email', code: 'required', message: 'Email is required.'),
                                                                   new DataTransferViolation(path: 'email', code: 'invalid', message: 'Email is invalid.'),
                                                               ]);

        // Act
        $errors = $violations->toLegacyErrors();

        // Assert
        self::assertSame(expected: ['email' => 'Email is invalid.'], actual: $errors);
    }

    public function test_it_exposes_structured_violation_data_when_failure_is_created_with_single_violation() : void
    {
        // Act
        $failure = DataTransferFailure::withViolation(
            path        : 'role',
            code        : 'invalid_enum',
            message     : 'Role is not supported.',
            expectedType: 'UnitRole',
            actualType  : 'string',
            failedRule  : 'enum',
        );

        // Assert
        $violation = $failure->violations()->all()[0];

        self::assertSame(expected: 'role', actual: $violation->path);
        self::assertSame(expected: 'invalid_enum', actual: $violation->code);
        self::assertSame(expected: 'UnitRole', actual: $violation->expectedType);
        self::assertStringContainsString(needle: 'role: Role is not supported.', haystack: $failure->getMessage());
    }
}
