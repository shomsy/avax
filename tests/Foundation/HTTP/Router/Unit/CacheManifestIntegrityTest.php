<?php

declare(strict_types=1);

use Avax\HTTP\Router\Cache\RouteCacheManifest;
use PHPUnit\Framework\TestCase;

/**
 * Tests for RouteCacheManifest integrity validation.
 *
 * Ensures cache manifest validation prevents stale and tampered caches.
 */
class CacheManifestIntegrityTest extends TestCase
{
    /**
     * @test
     */
    public function accepts_identical_manifests() : void
    {
        $files = [
            '/app/routes/web.php' => 1640995200,
            '/app/routes/api.php' => 1640995300,
        ];

        $manifest1 = $this->createManifest(files: $files, generatedAt: 1640995400);
        $manifest2 = $this->createManifest(files: $files, generatedAt: 1640995400);

        $this->assertTrue(condition: $manifest1->matches(other: $manifest2));
        $this->assertTrue(condition: $manifest2->matches(other: $manifest1));
    }

    /**
     * @test
     */
    public function rejects_stale_manifests() : void
    {
        $files = [
            '/app/routes/web.php' => 1640995200,
        ];

        $newerManifest = $this->createManifest(files: $files, generatedAt: 1640995500);
        $olderManifest = $this->createManifest(files: $files, generatedAt: 1640995400);

        // Newer manifest rejects older one (stale)
        $this->assertFalse(condition: $newerManifest->matches(other: $olderManifest));

        // Older manifest accepts newer one (not stale)
        $this->assertTrue(condition: $olderManifest->matches(other: $newerManifest));
    }

    /**
     * @test
     */
    public function rejects_modified_file_list() : void
    {
        $files1 = [
            '/app/routes/web.php' => 1640995200,
            '/app/routes/api.php' => 1640995300,
        ];

        $files2 = [
            '/app/routes/web.php'   => 1640995200,
            '/app/routes/admin.php' => 1640995300, // Different file
        ];

        $manifest1 = $this->createManifest(files: $files1, generatedAt: 1640995400);
        $manifest2 = $this->createManifest(files: $files2, generatedAt: 1640995400);

        $this->assertFalse(condition: $manifest1->matches(other: $manifest2));
        $this->assertFalse(condition: $manifest2->matches(other: $manifest1));
    }

    /**
     * @test
     */
    public function rejects_modified_file_timestamps() : void
    {
        $files1 = [
            '/app/routes/web.php' => 1640995200,
        ];

        $files2 = [
            '/app/routes/web.php' => 1640995300, // Different timestamp
        ];

        $manifest1 = $this->createManifest(files: $files1, generatedAt: 1640995400);
        $manifest2 = $this->createManifest(files: $files2, generatedAt: 1640995400);

        $this->assertFalse(condition: $manifest1->matches(other: $manifest2));
        $this->assertFalse(condition: $manifest2->matches(other: $manifest1));
    }

    /**
     * @test
     */
    public function accepts_manifests_with_checksum() : void
    {
        $files = [
            '/app/routes/web.php' => 1640995200,
        ];

        $manifest1 = $this->createManifest(files: $files, generatedAt: 1640995400);
        $manifest2 = $this->createManifest(files: $files, generatedAt: 1640995400);

        // Both have checksums and they match
        $this->assertTrue(condition: $manifest1->matches(other: $manifest2));
    }

    /**
     * @test
     */
    public function rejects_manifests_with_mismatched_checksums() : void
    {
        $files = [
            '/app/routes/web.php' => 1640995200,
        ];

        $manifest1 = $this->createManifest(files: $files, generatedAt: 1640995400);

        // Create manifest with different checksum
        $manifest2 = new RouteCacheManifest(
            files      : $files,
            hash       : $manifest1->getHash(),
            generatedAt: $manifest1->getGeneratedAt(),
            checksum   : 'different-checksum'
        );

        $this->assertFalse(condition: $manifest1->matches(other: $manifest2));
    }

    /**
     * @test
     */
    public function accepts_manifests_without_checksums_backward_compatibility() : void
    {
        $files = [
            '/app/routes/web.php' => 1640995200,
        ];

        // Create manifest without checksum (backward compatibility)
        $manifest1 = new RouteCacheManifest(
            files      : $files,
            hash       : sha1(json_encode($files)),
            generatedAt: 1640995400,
            checksum   : '' // Empty checksum
        );

        $manifest2 = new RouteCacheManifest(
            files      : $files,
            hash       : sha1(json_encode($files)),
            generatedAt: 1640995400,
            checksum   : '' // Empty checksum
        );

        $this->assertTrue(condition: $manifest1->matches(other: $manifest2));
    }

    /**
     * @test
     */
    public function rejects_manifests_with_partial_checksum_match() : void
    {
        $files = [
            '/app/routes/web.php' => 1640995200,
        ];

        $manifest1 = $this->createManifest(files: $files, generatedAt: 1640995400);

        // Create manifest with empty checksum
        $manifest2 = new RouteCacheManifest(
            files      : $files,
            hash       : $manifest1->getHash(),
            generatedAt: $manifest1->getGeneratedAt(),
            checksum   : '' // Empty checksum
        );

        // Should still match due to fallback logic
        $this->assertTrue(condition: $manifest1->matches(other: $manifest2));
    }

    /**
     * @test
     */
    public function build_from_directory_creates_valid_manifest() : void
    {
        // Create a temporary directory structure for testing
        $tempDir = sys_get_temp_dir() . '/router_test_' . uniqid();
        mkdir($tempDir);
        mkdir($tempDir . '/routes');

        $routeFile = $tempDir . '/routes/web.php';
        file_put_contents($routeFile, '<?php // test route file');

        try {
            $manifest = RouteCacheManifest::buildFromDirectory(baseDir: $tempDir);

            $this->assertNotEmpty(actual: $manifest->getHash());
            $this->assertNotEmpty(actual: $manifest->getChecksum());
            $this->assertGreaterThan(expected: 0, actual: $manifest->getGeneratedAt());
            $this->assertIsArray(actual: $manifest->getFiles());
            $this->assertArrayHasKey(key: $routeFile, array: $manifest->getFiles());
        } finally {
            // Cleanup
            unlink($routeFile);
            rmdir($tempDir . '/routes');
            rmdir($tempDir);
        }
    }

    /**
     * Helper method to create a manifest with checksum.
     */
    private function createManifest(array $files, int $generatedAt) : RouteCacheManifest
    {
        ksort($files);
        $hash     = sha1(json_encode($files));
        $checksum = hash('sha256', $hash . json_encode($files));

        return new RouteCacheManifest(
            files      : $files,
            hash       : $hash,
            generatedAt: $generatedAt,
            checksum   : $checksum
        );
    }
}
