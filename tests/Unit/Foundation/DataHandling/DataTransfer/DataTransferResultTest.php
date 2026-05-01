<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Foundation\DataHandling\DataTransfer;

use Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferFailure;
use Avax\DataHandling\DataTransfer\DataTransferException;
use Avax\DataHandling\DataTransfer\DataTransferResult;
use PHPUnit\Framework\TestCase;
use stdClass;

final class DataTransferResultTest extends TestCase
{
    public function test_it_returns_created_object_when_result_is_successful() : void
    {
        // Arrange
        $object = new stdClass;

        // Act
        $result = DataTransferResult::success(object: $object);

        // Assert
        self::assertTrue(condition: $result->isSuccess());
        self::assertFalse(condition: $result->isFailure());
        self::assertSame(expected: $object, actual: $result->object());
    }

    public function test_it_returns_failure_reason_when_result_is_failed() : void
    {
        // Arrange
        $failure = new DataTransferFailure(message: 'Could not create data object.');

        // Act
        $result = DataTransferResult::failure(failure: $failure);

        // Assert
        self::assertFalse(condition: $result->isSuccess());
        self::assertTrue(condition: $result->isFailure());
        self::assertSame(expected: $failure, actual: $result->failureReason());
    }

    public function test_it_throws_data_transfer_exception_when_object_is_requested_from_failed_result() : void
    {
        // Arrange
        $result = DataTransferResult::failure(
            failure: new DataTransferFailure(message: 'Could not create data object.'),
        );

        // Assert
        $this->expectException(exception: DataTransferException::class);
        $this->expectExceptionMessage(message: 'Data transfer did not produce an object.');

        // Act
        $result->object();
    }

    public function test_it_throws_data_transfer_exception_when_failure_reason_is_requested_from_successful_result() : void
    {
        // Arrange
        $result = DataTransferResult::success(object: new stdClass);

        // Assert
        $this->expectException(exception: DataTransferException::class);
        $this->expectExceptionMessage(message: 'Data transfer completed successfully.');

        // Act
        $result->failureReason();
    }
}
