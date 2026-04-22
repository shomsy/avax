<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\tests\Unit;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\GuardUploadedFiles;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\NormalizeUploadedFiles;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\UploadedFile;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class UploadedFilesHardeningTest extends TestCase
{
    public function test_normalize_uploaded_files_accepts_uploaded_file_interface_instances()
    {
        $file       = new UploadedFile(tmpName: 'php://temp', size: 0, error: UPLOAD_ERR_OK);
        $normalizer = new NormalizeUploadedFiles;

        $result = $normalizer->execute(files: ['avatar' => $file]);
        $this->assertSame(expected: $file, actual: $result['avatar']);
    }

    public function test_normalize_uploaded_files_normalizes_flat_php_files_spec()
    {
        // Simulation of $_FILES['avatar']
        $files = [
            'avatar' => [
                'tmp_name' => 'php://temp',
                'size'     => 10,
                'error'    => UPLOAD_ERR_OK,
                'name'     => 'me.png',
                'type'     => 'image/png',
            ],
        ];

        $normalizer = new NormalizeUploadedFiles;
        $result     = $normalizer->execute(files: $files);

        $this->assertInstanceOf(expected: UploadedFile::class, actual: $result['avatar']);
        $this->assertEquals(expected: 'me.png', actual: $result['avatar']->getClientFilename());
    }

    public function test_guard_uploaded_files_rejects_invalid_leaf()
    {
        $guard = new GuardUploadedFiles;
        $this->expectException(InvalidArgumentException::class);
        $guard->execute(files: ['avatar' => 'not-a-file']);
    }

    public function test_uploaded_file_rejects_move_when_error_state()
    {
        $file   = new UploadedFile(tmpName: 'php://temp', size: 0, error: UPLOAD_ERR_NO_FILE);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot move file with upload error');
        $file->moveTo(targetPath: '/tmp/foo');
    }
}
