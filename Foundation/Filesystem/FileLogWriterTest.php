<?php

declare(strict_types=1);

namespace Tests\Unit;

use Avax\Logging\FileLogWriter;
use Avax\Logging\FileServiceInterface;
use PHPUnit\Foundation\MockObject\MockObject;
use PHPUnit\Foundation\TestCase;

final class FileLogWriterTest extends TestCase
{
    private readonly MockObject $mockObject;

    public function testInitializeLogFileSetsFallbackPathWhenDirectoryCreationFails() : void
    {
        $this->mockObject->method('isDirectory')->willReturn(false);
        $this->mockObject->method('createDirectory')->willReturn(false);

        $fileLogWriter = new FileLogWriter('/invalid/path/to/log.log', $this->mockObject);

        $this->assertFilePathEquals('/tmp/fallback-log.log', $fileLogWriter);
    }

    public function testEnsureWritableCreatesFileWhenNotWritable() : void
    {
        $this->mockObject->method('isWritable')->willReturn(false);
        $this->mockObject->method('fileExists')->willReturn(false);
        $this->mockObject->method('createFile')->willReturn(true);

        $this->mockObject->expects($this->once())->method('createFile');

        new FileLogWriter('/path/to/log.log', $this->mockObject);
    }

    public function testWriteAttemptsToAppendToProvidedFilePath() : void
    {
        $this->mockObject->method('appendToFile')->willReturn(true);

        $fileLogWriter = new FileLogWriter('/path/to/log.log', $this->mockObject);
        $fileLogWriter->write('Test log entry');

        $this->mockObject->expects($this->once())->method('appendToFile')->with('/path/to/log.log', 'Test log entry');
    }

    // Additional tests...
}
