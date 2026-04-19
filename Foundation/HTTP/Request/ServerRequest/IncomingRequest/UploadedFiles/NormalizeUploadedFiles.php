<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles;

use Avax\HTTP\Response\Classes\Stream;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Action Owner: Normalizes raw $_FILES array into a tree of UploadedFileInterface objects.
 */
final readonly class NormalizeUploadedFiles
{
    /**
     * @param array $files Raw files array from $_FILES or similar.
     *
     * @return array<string, UploadedFileInterface|array>
     */
    public function execute(array $files) : array
    {
        $normalized = [];

        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;

                continue;
            }

            if (is_array($value) && isset($value['tmp_name'])) {
                $normalized[$key] = $this->createUploadedFileFromSpec(value: $value);

                continue;
            }

            if (is_array($value)) {
                $normalized[$key] = $this->execute(files: $value);

            }
        }

        return $normalized;
    }

    private function createUploadedFileFromSpec(array $value) : array|UploadedFileInterface
    {
        if (is_array($value['tmp_name'])) {
            return $this->normalizeNestedFileSpec(files: $value);
        }

        return new UploadedFile(
            stream         : new Stream(stream: fopen($value['tmp_name'], 'r')),
            size           : $value['size'],
            error          : $value['error'],
            clientFilename : $value['name'],
            clientMediaType: $value['type']
        );
    }

    private function normalizeNestedFileSpec(array $files) : array
    {
        $normalized = [];

        foreach (array_keys($files['tmp_name']) as $key) {
            $spec = [
                'tmp_name' => $files['tmp_name'][$key],
                'size'     => $files['size'][$key],
                'error'    => $files['error'][$key],
                'name'     => $files['name'][$key],
                'type'     => $files['type'][$key],
            ];

            $normalized[$key] = $this->createUploadedFileFromSpec(value: $spec);
        }

        return $normalized;
    }
}
