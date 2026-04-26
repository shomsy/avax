<?php

declare(strict_types=1);

namespace components\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles;

use InvalidArgumentException;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Action Owner: Recursively validates the uploaded files tree structure.
 */
final readonly class GuardUploadedFiles
{
    /**
     * @param array $files The tree of uploaded files.
     *
     * @throws InvalidArgumentException If the tree contains invalid elements.
     */
    public function execute(array $files) : void
    {
        foreach ($files as $file) {
            if (is_array(value: $file)) {
                $this->execute(files: $file);

                continue;
            }

            if (! $file instanceof UploadedFileInterface) {
                throw new InvalidArgumentException(
                    message: 'Invalid uploaded file encountered in the tree.'
                );
            }
        }
    }
}
