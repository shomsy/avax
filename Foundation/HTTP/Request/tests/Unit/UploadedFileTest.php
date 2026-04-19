<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\tests\Unit;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\UploadedFile;
use Avax\HTTP\Response\Classes\Stream;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class UploadedFileTest extends TestCase
{
    public function test_move_to_moves_file_once()
    {
        $file   = $this->createUploadedFile();
        $target = sys_get_temp_dir() . '/test_move_' . uniqid();

        $file->moveTo(targetPath: $target);

        $this->assertTrue(condition: file_exists($target));
        unlink($target);
    }

    private function createUploadedFile() : UploadedFile
    {
        $path = sys_get_temp_dir() . '/uploaded_test_' . uniqid();
        file_put_contents($path, 'dummy content');

        return new UploadedFile(
            tmpName: $path,
            size: 13,
            error: UPLOAD_ERR_OK,
            name: 'test.txt',
            type: 'text/plain'
        );
    }

    public function test_move_to_throws_when_called_twice()
    {
        $file    = $this->createUploadedFile();
        $target1 = sys_get_temp_dir() . '/test_move_1_' . uniqid();
        $target2 = sys_get_temp_dir() . '/test_move_2_' . uniqid();

        $file->moveTo(targetPath: $target1);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File already moved');

        try {
            $file->moveTo(targetPath: $target2);
        } finally {
            unlink($target1);
            unlink($target2);
        }
    }

    public function test_get_stream_throws_after_move()
    {
        $file   = $this->createUploadedFile();
        $target = sys_get_temp_dir() . '/test_move_' . uniqid();

        $file->moveTo(targetPath: $target);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File already moved');

        try {
            $file->getStream();
        } finally {
            unlink($target);
        }
    }

    public function test_move_to_rejects_empty_target_path()
    {
        $file = $this->createUploadedFile();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid target path');

        $file->moveTo(targetPath: '');
    }
}
