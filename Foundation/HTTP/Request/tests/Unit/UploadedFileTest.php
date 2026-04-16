<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\UploadedFile;
use Avax\HTTP\Response\Classes\Stream;
use RuntimeException;
use InvalidArgumentException;

class UploadedFileTest extends TestCase
{
    private function createUploadedFile(int $error = UPLOAD_ERR_OK): UploadedFile
    {
        $stream = new Stream(stream: fopen('php://temp', 'r+'));
        $stream->write(string: 'dummy content');
        return new UploadedFile(
            stream: $stream,
            size: 13,
            error: $error,
            clientFilename: 'test.txt',
            clientMediaType: 'text/plain'
        );
    }

    public function test_move_to_moves_file_once()
    {
        $file = $this->createUploadedFile();
        $target = sys_get_temp_dir() . '/test_move_' . uniqid();
        
        $file->moveTo($target);
        
        $this->assertTrue(condition: file_exists($target));
        @unlink($target);
    }

    public function test_move_to_throws_when_called_twice()
    {
        $file = $this->createUploadedFile();
        $target1 = sys_get_temp_dir() . '/test_move_1_' . uniqid();
        $target2 = sys_get_temp_dir() . '/test_move_2_' . uniqid();
        
        $file->moveTo($target1);
        
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File already moved');
        
        try {
            $file->moveTo($target2);
        } finally {
            @unlink($target1);
            @unlink($target2);
        }
    }

    public function test_get_stream_throws_after_move()
    {
        $file = $this->createUploadedFile();
        $target = sys_get_temp_dir() . '/test_move_' . uniqid();
        
        $file->moveTo($target);
        
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File already moved');
        
        try {
            $file->getStream();
        } finally {
            @unlink($target);
        }
    }

    public function test_move_to_rejects_empty_target_path()
    {
        $file = $this->createUploadedFile();
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid target path');
        
        $file->moveTo('');
    }
}
