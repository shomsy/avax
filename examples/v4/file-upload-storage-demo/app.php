<?php

declare(strict_types=1);

/**
 * File Upload & Storage Demo reference application.
 *
 * Proves: Storage, Filesystem, validation concepts.
 *
 * Run: php examples/v4/file-upload-storage-demo/app.php
 */

require __DIR__ . '/../../../vendor/autoload.php';

use Avax\Framework\System\PublicSurface\Avax;

$app = Avax::create();

// Simulated file storage (filesystem abstraction)
$files = [];
$fileCounter = 0;

$app->get('/health', static fn () => ['status' => 'ok']);

$app->post('/upload', static function () use (&$files, &$fileCounter) {
    // Simulate file upload validation
    $maxSize = 10 * 1024 * 1024; // 10 MB
    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf', 'text/plain'];

    // In a real app, read from $_FILES; here we simulate from POST data
    $fileName = $_POST['filename'] ?? 'uploaded-file.txt';
    $fileSize = (int) ($_POST['filesize'] ?? rand(1024, 1048576));
    $fileType = $_POST['filetype'] ?? 'text/plain';

    // Validation: file size
    if ($fileSize > $maxSize) {
        return [
            'error' => 'File too large',
            'max_size' => $maxSize,
            'actual_size' => $fileSize,
        ];
    }

    // Validation: file type
    if (!in_array($fileType, $allowedTypes, true)) {
        return [
            'error' => 'File type not allowed',
            'allowed_types' => $allowedTypes,
            'actual_type' => $fileType,
        ];
    }

    // Validation: file name
    if (strlen($fileName) > 255) {
        return ['error' => 'File name too long', 'max_length' => 255];
    }

    // Store the file metadata (simulates writing to filesystem + DB record)
    $fileCounter++;
    $fileId = 'file-' . $fileCounter;

    $file = [
        'id' => $fileId,
        'name' => $fileName,
        'type' => $fileType,
        'size' => $fileSize,
        'path' => '/storage/uploads/' . date('Y/m/d') . '/' . $fileId . '-' . $fileName,
        'uploaded_at' => date('c'),
        'checksum' => hash('sha256', $fileName . $fileSize . time()),
    ];

    $files[$fileId] = $file;

    return [
        'status' => 'uploaded',
        'file' => $file,
    ];
});

$app->get('/files/{id}', static function ($id) use (&$files) {
    if (!isset($files[$id])) {
        return ['error' => 'File not found', 'id' => $id];
    }

    return [
        'file' => $files[$id],
        'storage' => 'local',
        'available' => true,
    ];
});

$app->delete('/files/{id}', static function ($id) use (&$files) {
    if (!isset($files[$id])) {
        return ['error' => 'File not found', 'id' => $id];
    }

    unset($files[$id]);

    return [
        'status' => 'deleted',
        'id' => $id,
    ];
});

$GLOBALS['_APP_RUN'] = false;

if (php_sapi_name() === 'cli' && $GLOBALS['_APP_RUN'] ?? true) {
    $app->run();
}
