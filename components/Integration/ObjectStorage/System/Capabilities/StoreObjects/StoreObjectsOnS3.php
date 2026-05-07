<?php

declare(strict_types=1);

namespace Avax\Components\Integration\ObjectStorage\System\Capabilities\StoreObjects;

use Avax\Components\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStoragePort;
use Avax\Components\Integration\ObjectStorage\System\Capabilities\Ports\ObjectStorageResult;
use Aws\PresignUrlMiddleware;
use Aws\S3\S3Client;
use Throwable;

class StoreObjectsOnS3 implements ObjectStoragePort
{
    private string $bucket;

    private string $region;

    private ?string $endpoint;

    private object $client;

    private object $presigner;

    public function __construct(
        string  $bucket,
        string  $region = 'us-east-1',
        ?string $endpoint = null,
    )
    {
        $this->bucket   = $bucket;
        $this->region   = $region;
        $this->endpoint = $endpoint;
    }

    public function store(string $key, string $content, array $options = []) : ObjectStorageResult
    {
        try {
            $this->client->putObject([
                                         'Bucket'      => $this->bucket,
                                         'Key'         => $key,
                                         'Body'        => $content,
                                         'ContentType' => $options['contentType'] ?? 'application/octet-stream',
                                     ]);

            return ObjectStorageResult::success();
        } catch (Throwable $e) {
            return ObjectStorageResult::failure($e->getMessage());
        }
    }

    public function read(string $key) : ?string
    {
        try {
            $result = $this->client->getObject([
                                                   'Bucket' => $this->bucket,
                                                   'Key'    => $key,
                                               ]);

            return $result['Body']->getContents();
        } catch (Throwable $e) {
            return null;
        }
    }

    public function delete(string $key) : bool
    {
        try {
            $this->client->deleteObject([
                                            'Bucket' => $this->bucket,
                                            'Key'    => $key,
                                        ]);

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function exists(string $key) : bool
    {
        try {
            $this->client->headObject([
                                          'Bucket' => $this->bucket,
                                          'Key'    => $key,
                                      ]);

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function generatePresignedUrl(string $key, int $expiresInSeconds) : string
    {
        $command = $this->client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key'    => $key,
        ]);

        return $this->presigner->createPresignedRequest($command, $expiresInSeconds)->getUri();
    }

    public function healthCheck() : bool
    {
        try {
            $this->client->headBucket(['Bucket' => $this->bucket]);

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    private function presigner() : object
    {
        if (! isset($this->presigner)) {
            $this->presigner = new PresignUrlMiddleware(
                $this->client(),
                's3'
            );
        }

        return $this->presigner;
    }

    private function client() : object
    {
        if (! isset($this->client)) {
            $config = [
                'region'  => $this->region,
                'version' => 'latest',
            ];
            if ($this->endpoint) {
                $config['endpoint'] = $this->endpoint;
            }
            $this->client = new S3Client($config);
        }

        return $this->client;
    }
}
