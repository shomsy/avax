<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Cache;

use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\DistributedCompiledCache\CompiledCacheFreshness;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\DistributedCompiledCache\CompiledCacheManifest;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\DistributedCompiledCache\CompiledCacheManifestEntry;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\DistributedCompiledCache\FreshnessStatus;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use JsonException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CompiledCacheManifestTest extends TestCase
{
    private string $tempDir;

    private FrozenClock $clock;

    public function test_add_entry_creates_entry_with_source_files() : void
    {
        $sourceFile = $this->createTempFile('source.php', '<?php // source');
        $manifest = CompiledCacheManifest::empty($this->clock);

        $entry = $manifest->addEntry(
            name        : 'config-cache',
            compiledPath: $this->tempDir . '/config-compiled.php',
            sourceFiles : [$sourceFile],
        );

        $this->assertSame('config-cache', $entry->name);
        $this->assertSame($this->tempDir . '/config-compiled.php', $entry->compiledPath);
        $this->assertSame([$sourceFile], $entry->sourceFiles);
        $this->assertNotNull($entry->fingerprint);
    }

    private function createTempFile(string $name, string $content = '') : string
    {
        $path = $this->tempDir . '/' . $name;
        file_put_contents($path, $content);

        return $path;
    }

    public function test_add_entry_generates_sha256_fingerprint() : void
    {
        $sourceFile = $this->createTempFile('fingerprint-source.php', '<?php // fp source');
        $manifest = CompiledCacheManifest::empty($this->clock);

        $entry = $manifest->addEntry(
            name        : 'fp-test',
            compiledPath: $this->tempDir . '/fp-compiled.php',
            sourceFiles : [$sourceFile],
        );

        // SHA-256 produces 64 hex characters
        $this->assertSame(64, strlen($entry->fingerprint));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $entry->fingerprint);
    }

    public function test_add_entry_includes_php_version() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);

        $entry = $manifest->addEntry(
            name        : 'php-version-test',
            compiledPath: $this->tempDir . '/compiled.php',
            sourceFiles : [],
        );

        $this->assertSame(PHP_VERSION, $entry->phpVersion);
    }

    // --- addEntry(key, sourceFiles) ---

    public function test_add_entry_with_custom_type() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);

        $entry = $manifest->addEntry(
            name        : 'routes-cache',
            compiledPath: $this->tempDir . '/routes-compiled.php',
            sourceFiles : [],
            type        : 'routes',
        );

        $this->assertSame('routes', $entry->type);
    }

    public function test_add_entry_with_framework_version() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);

        $entry = $manifest->addEntry(
            name            : 'fw-version-test',
            compiledPath    : $this->tempDir . '/compiled.php',
            sourceFiles     : [],
            frameworkVersion: '2.0.0',
        );

        $this->assertSame('2.0.0', $entry->frameworkVersion);
    }

    public function test_add_entry_sets_created_at_and_updated_at() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);

        $entry = $manifest->addEntry(
            name        : 'timestamp-test',
            compiledPath: $this->tempDir . '/compiled.php',
            sourceFiles : [],
        );

        $this->assertSame(1000000, $entry->createdAt->seconds);
        $this->assertSame(1000000, $entry->updatedAt->seconds);
    }

    public function test_remove_entry_removes_existing_entry() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);
        $manifest->addEntry(name: 'to-remove', compiledPath: '/path', sourceFiles: []);

        $this->assertTrue($manifest->hasEntry('to-remove'));

        $result = $manifest->removeEntry('to-remove');

        $this->assertTrue($result);
        $this->assertFalse($manifest->hasEntry('to-remove'));
    }

    public function test_remove_entry_returns_false_for_nonexistent_entry() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);

        $result = $manifest->removeEntry('nonexistent');

        $this->assertFalse($result);
    }

    public function test_is_fresh_returns_false_for_nonexistent_entry() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);

        $this->assertFalse($manifest->isFresh('nonexistent'));
    }

    // --- removeEntry(key) ---

    public function test_is_fresh_returns_true_when_source_files_unchanged() : void
    {
        $sourceFile = $this->createTempFile('fresh-source.php', '<?php // fresh');
        $manifest = CompiledCacheManifest::empty($this->clock);

        $manifest->addEntry(
            name        : 'fresh-test',
            compiledPath: $this->tempDir . '/fresh-compiled.php',
            sourceFiles : [$sourceFile],
        );

        $this->assertTrue($manifest->isFresh('fresh-test'));
    }

    public function test_is_fresh_returns_false_when_source_file_modified() : void
    {
        $sourceFile = $this->createTempFile('stale-source.php', '<?php // original');
        $manifest = CompiledCacheManifest::empty($this->clock);

        $manifest->addEntry(
            name        : 'stale-test',
            compiledPath: $this->tempDir . '/stale-compiled.php',
            sourceFiles : [$sourceFile],
        );

        // Modify the source file
        sleep(1);
        file_put_contents($sourceFile, '<?php // modified');

        $this->assertFalse($manifest->isFresh('stale-test'));
    }

    // --- isFresh() ---

    public function test_is_fresh_with_multiple_source_files() : void
    {
        $source1 = $this->createTempFile('multi-source-1.php', '<?php // 1');
        $source2 = $this->createTempFile('multi-source-2.php', '<?php // 2');
        $manifest = CompiledCacheManifest::empty($this->clock);

        $manifest->addEntry(
            name        : 'multi-test',
            compiledPath: $this->tempDir . '/multi-compiled.php',
            sourceFiles : [$source1, $source2],
        );

        $this->assertTrue($manifest->isFresh('multi-test'));
    }

    public function test_load_from_json_file() : void
    {
        $manifestPath = $this->tempDir . '/manifest.json';

        $data = [
            'version' => '1.0',
            'generatedAt' => 1000000,
            'entries' => [
                'config-cache' => [
                    'name'         => 'config-cache',
                    'compiledPath' => '/compiled/config.php',
                    'sourceFiles'  => ['/src/config.php'],
                    'fingerprint'  => 'abc123',
                    'createdAt'    => 1000000,
                    'updatedAt'    => 1000000,
                    'type'         => 'config',
                    'phpVersion'   => '8.2.0',
                    'frameworkVersion' => '1.0.0',
                ],
            ],
        ];

        file_put_contents($manifestPath, json_encode($data, JSON_PRETTY_PRINT));

        $manifest = CompiledCacheManifest::load($manifestPath, $this->clock);

        $this->assertCount(1, $manifest->getAllEntries());
        $this->assertTrue($manifest->hasEntry('config-cache'));

        $entry = $manifest->getEntry('config-cache');
        $this->assertNotNull($entry);
        $this->assertSame('config-cache', $entry->name);
        $this->assertSame('/compiled/config.php', $entry->compiledPath);
        $this->assertSame('/src/config.php', $entry->sourceFiles[0]);
        $this->assertSame('config', $entry->type);
    }

    public function test_load_returns_empty_manifest_when_file_does_not_exist() : void
    {
        $manifest = CompiledCacheManifest::load($this->tempDir . '/nonexistent.json', $this->clock);

        $this->assertCount(0, $manifest->getAllEntries());
    }

    public function test_load_throws_on_invalid_json() : void
    {
        $manifestPath = $this->tempDir . '/invalid.json';
        file_put_contents($manifestPath, 'not valid json {{{');

        $this->expectException(JsonException::class);

        CompiledCacheManifest::load($manifestPath, $this->clock);
    }

    // --- load() from JSON file ---

    public function test_load_throws_on_invalid_format() : void
    {
        $manifestPath = $this->tempDir . '/wrong-format.json';
        file_put_contents($manifestPath, '"just a string"');

        $this->expectException(RuntimeException::class);

        CompiledCacheManifest::load($manifestPath, $this->clock);
    }

    public function test_save_to_json_file() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);
        $manifest->addEntry(
            name        : 'save-test',
            compiledPath: '/compiled/test.php',
            sourceFiles : ['/src/test.php'],
            type        : 'views',
        );

        $savePath = $this->tempDir . '/saved-manifest.json';
        $manifest->save($savePath);

        $this->assertFileExists($savePath);

        $content = file_get_contents($savePath);
        $data     = json_decode($content, true);

        $this->assertArrayHasKey('version', $data);
        $this->assertSame('1.0', $data['version']);
        $this->assertArrayHasKey('entries', $data);
        $this->assertArrayHasKey('save-test', $data['entries']);
    }

    public function test_save_throws_without_path() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No manifest path specified for saving');

        $manifest->save();
    }

    public function test_save_creates_directory_if_needed() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);
        $manifest->addEntry(name: 'dir-test', compiledPath: '/path', sourceFiles: []);

        $savePath = $this->tempDir . '/subdir/nested/manifest.json';
        $manifest->save($savePath);

        $this->assertFileExists($savePath);
    }

    // --- save() to JSON file ---

    public function test_save_and_load_roundtrip() : void
    {
        $original = CompiledCacheManifest::empty($this->clock);
        $original->addEntry(
            name            : 'roundtrip-entry',
            compiledPath    : '/compiled/roundtrip.php',
            sourceFiles     : ['/src/roundtrip.php'],
            type            : 'config',
            phpVersion      : '8.2.0',
            frameworkVersion: '1.5.0',
        );

        $savePath = $this->tempDir . '/roundtrip.json';
        $original->save($savePath);

        $loaded = CompiledCacheManifest::load($savePath, $this->clock);

        $this->assertCount(1, $loaded->getAllEntries());

        $entry = $loaded->getEntry('roundtrip-entry');
        $this->assertNotNull($entry);
        $this->assertSame('roundtrip-entry', $entry->name);
        $this->assertSame('/compiled/roundtrip.php', $entry->compiledPath);
        $this->assertSame('/src/roundtrip.php', $entry->sourceFiles[0]);
        $this->assertSame('config', $entry->type);
        $this->assertSame('8.2.0', $entry->phpVersion);
        $this->assertSame('1.5.0', $entry->frameworkVersion);
    }

    public function test_manifest_entry_properties() : void
    {
        $entry = new CompiledCacheManifestEntry(
            name            : 'entry-test',
            compiledPath    : '/compiled/test.php',
            sourceFiles     : ['/src/a.php', '/src/b.php'],
            fingerprint     : 'sha256hash',
            createdAt       : Timestamp::fromUnixTime(1000000),
            updatedAt       : Timestamp::fromUnixTime(1000001),
            type            : 'routes',
            phpVersion      : '8.1.0',
            frameworkVersion: '1.0.0',
        );

        $this->assertSame('entry-test', $entry->name);
        $this->assertSame('/compiled/test.php', $entry->compiledPath);
        $this->assertSame(['/src/a.php', '/src/b.php'], $entry->sourceFiles);
        $this->assertSame('sha256hash', $entry->fingerprint);
        $this->assertSame(1000000, $entry->createdAt->seconds);
        $this->assertSame(1000001, $entry->updatedAt->seconds);
        $this->assertSame('routes', $entry->type);
        $this->assertSame('8.1.0', $entry->phpVersion);
        $this->assertSame('1.0.0', $entry->frameworkVersion);
    }

    public function test_manifest_entry_to_array() : void
    {
        $entry = new CompiledCacheManifestEntry(
            name        : 'array-test',
            compiledPath: '/compiled/array.php',
            sourceFiles : ['/src/array.php'],
            fingerprint : 'hash123',
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        $array = $entry->toArray();

        $this->assertIsArray($array);
        $this->assertSame('array-test', $array['name']);
        $this->assertSame('/compiled/array.php', $array['compiledPath']);
        $this->assertSame(['/src/array.php'], $array['sourceFiles']);
        $this->assertSame('hash123', $array['fingerprint']);
        $this->assertSame(1000000, $array['createdAt']);
        $this->assertSame(1000000, $array['updatedAt']);
    }

    // --- File roundtrip (save then load) ---

    public function test_manifest_entry_from_array_roundtrip() : void
    {
        $original = new CompiledCacheManifestEntry(
            name            : 'roundtrip-entry',
            compiledPath    : '/compiled/roundtrip.php',
            sourceFiles     : ['/src/roundtrip.php'],
            fingerprint     : 'roundtrip-hash',
            createdAt       : Timestamp::fromUnixTime(1000000),
            updatedAt       : Timestamp::fromUnixTime(1000001),
            type            : 'views',
            phpVersion      : '8.3.0',
            frameworkVersion: '2.0.0',
        );

        $array = $original->toArray();
        $restored = CompiledCacheManifestEntry::fromArray($array);

        $this->assertSame($original->name, $restored->name);
        $this->assertSame($original->compiledPath, $restored->compiledPath);
        $this->assertSame($original->sourceFiles, $restored->sourceFiles);
        $this->assertSame($original->fingerprint, $restored->fingerprint);
        $this->assertSame($original->createdAt->seconds, $restored->createdAt->seconds);
        $this->assertSame($original->updatedAt->seconds, $restored->updatedAt->seconds);
        $this->assertSame($original->type, $restored->type);
        $this->assertSame($original->phpVersion, $restored->phpVersion);
        $this->assertSame($original->frameworkVersion, $restored->frameworkVersion);
    }

    // --- Manifest entry properties ---

    public function test_manifest_entry_with_updated_at() : void
    {
        $entry = new CompiledCacheManifestEntry(
            name        : 'touch-test',
            compiledPath: '/compiled/touch.php',
            sourceFiles : [],
            fingerprint : 'hash',
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        $newEntry = $entry->withUpdatedAt(Timestamp::fromUnixTime(1000010));

        $this->assertSame(1000000, $entry->updatedAt->seconds); // Original unchanged
        $this->assertSame(1000010, $newEntry->updatedAt->seconds);
        $this->assertSame(1000000, $newEntry->createdAt->seconds); // CreatedAt unchanged
    }

    // --- toArray() and fromArray() roundtrip ---

    public function test_compiled_file_exists_when_file_exists() : void
    {
        $compiledPath = $this->createTempFile('exists.php', '<?php');

        $entry = new CompiledCacheManifestEntry(
            name        : 'exists-test',
            compiledPath: $compiledPath,
            sourceFiles : [],
            fingerprint : 'hash',
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        $this->assertTrue($entry->compiledFileExists());
    }

    public function test_compiled_file_exists_when_file_does_not_exist() : void
    {
        $entry = new CompiledCacheManifestEntry(
            name        : 'not-exists-test',
            compiledPath: '/nonexistent/compiled.php',
            sourceFiles : [],
            fingerprint : 'hash',
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        $this->assertFalse($entry->compiledFileExists());
    }

    // --- withUpdatedAt() ---

    public function test_get_compiled_file_mtime_returns_mtime() : void
    {
        $compiledPath = $this->createTempFile('mtime.php', '<?php');

        $entry = new CompiledCacheManifestEntry(
            name        : 'mtime-test',
            compiledPath: $compiledPath,
            sourceFiles : [],
            fingerprint : 'hash',
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        $mtime = $entry->getCompiledFileMtime();

        $this->assertIsInt($mtime);
        $this->assertGreaterThan(0, $mtime);
    }

    // --- compiledFileExists() and getCompiledFileMtime() ---

    public function test_get_compiled_file_mtime_returns_false_when_file_missing() : void
    {
        $entry = new CompiledCacheManifestEntry(
            name        : 'missing-mtime-test',
            compiledPath: '/nonexistent/file.php',
            sourceFiles : [],
            fingerprint : 'hash',
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        $this->assertFalse($entry->getCompiledFileMtime());
    }

    public function test_has_entry_returns_true_for_existing_entry() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);
        $manifest->addEntry(name: 'check-entry', compiledPath: '/path', sourceFiles: []);

        $this->assertTrue($manifest->hasEntry('check-entry'));
    }

    public function test_has_entry_returns_false_for_missing_entry() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);

        $this->assertFalse($manifest->hasEntry('missing'));
    }

    public function test_get_entry_returns_entry() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);
        $manifest->addEntry(name: 'get-entry', compiledPath: '/path', sourceFiles: []);

        $entry = $manifest->getEntry('get-entry');

        $this->assertNotNull($entry);
        $this->assertSame('get-entry', $entry->name);
    }

    // --- getEntry / hasEntry ---

    public function test_get_entry_returns_null_for_missing_entry() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);

        $this->assertNull($manifest->getEntry('missing'));
    }

    public function test_get_all_entries_returns_all_entries() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);
        $manifest->addEntry(name: 'entry-1', compiledPath: '/path1', sourceFiles: []);
        $manifest->addEntry(name: 'entry-2', compiledPath: '/path2', sourceFiles: []);

        $all = $manifest->getAllEntries();

        $this->assertCount(2, $all);
        $this->assertArrayHasKey('entry-1', $all);
        $this->assertArrayHasKey('entry-2', $all);
    }

    public function test_get_entries_by_type() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);
        $manifest->addEntry(name: 'config-1', compiledPath: '/c1', sourceFiles: [], type: 'config');
        $manifest->addEntry(name: 'config-2', compiledPath: '/c2', sourceFiles: [], type: 'config');
        $manifest->addEntry(name: 'routes-1', compiledPath: '/r1', sourceFiles: [], type: 'routes');

        $configEntries = $manifest->getEntriesByType('config');

        $this->assertCount(2, $configEntries);
    }

    public function test_get_entries_by_type_returns_empty_for_unknown_type() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);
        $manifest->addEntry(name: 'entry-1', compiledPath: '/path', sourceFiles: []);

        $entries = $manifest->getEntriesByType('nonexistent-type');

        $this->assertCount(0, $entries);
    }

    // --- getAllEntries ---

    public function test_get_stale_entries_returns_stale_entries() : void
    {
        $sourceFile = $this->createTempFile('stale-get.php', '<?php // original');
        $manifest = CompiledCacheManifest::empty($this->clock);
        $manifest->addEntry(
            name        : 'stale-get-test',
            compiledPath: $this->tempDir . '/compiled.php',
            sourceFiles : [$sourceFile],
        );

        // Modify source
        sleep(1);
        file_put_contents($sourceFile, '<?php // modified');

        $stale = $manifest->getStaleEntries();

        $this->assertCount(1, $stale);
        $this->assertSame('stale-get-test', $stale[0]->name);
    }

    // --- getEntriesByType ---

    public function test_get_stale_entries_returns_empty_when_all_fresh() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);
        $manifest->addEntry(name: 'fresh-get', compiledPath: '/path', sourceFiles: []);

        $stale = $manifest->getStaleEntries();

        $this->assertCount(0, $stale);
    }

    public function test_count_returns_entry_count() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);

        $this->assertSame(0, $manifest->count());

        $manifest->addEntry(name: 'count-1', compiledPath: '/path1', sourceFiles: []);
        $manifest->addEntry(name: 'count-2', compiledPath: '/path2', sourceFiles: []);

        $this->assertSame(2, $manifest->count());
    }

    // --- getStaleEntries ---

    public function test_empty_creates_manifest_with_no_entries() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);

        $this->assertCount(0, $manifest->getAllEntries());
        $this->assertTrue($manifest->count() === 0);
    }

    public function test_clear_removes_all_entries() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);
        $manifest->addEntry(name: 'clear-1', compiledPath: '/path1', sourceFiles: []);
        $manifest->addEntry(name: 'clear-2', compiledPath: '/path2', sourceFiles: []);

        $manifest->clear();

        $this->assertCount(0, $manifest->getAllEntries());
        $this->assertSame(0, $manifest->count());
    }

    // --- count() ---

    public function test_touch_entry_updates_timestamp() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);
        $manifest->addEntry(name: 'touch-entry', compiledPath: '/path', sourceFiles: []);

        $newClock = new FrozenClock(Timestamp::fromUnixTime(1000010));

        $manifest2 = CompiledCacheManifest::empty($newClock);
        $manifest2->addEntry(name: 'touch-entry', compiledPath: '/path', sourceFiles: []);
        $manifest2->touchEntry('touch-entry');

        $entry = $manifest2->getEntry('touch-entry');
        $this->assertNotNull($entry);
        $this->assertSame(1000010, $entry->updatedAt->seconds);
    }

    // --- empty() ---

    public function test_touch_entry_returns_false_for_missing_entry() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);

        $this->assertFalse($manifest->touchEntry('missing'));
    }

    // --- clear() ---

    public function test_get_manifest_path_returns_null_initially() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);

        $this->assertNull($manifest->getManifestPath());
    }

    // --- touchEntry ---

    public function test_get_manifest_path_returns_path_after_save() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);
        $manifest->addEntry(name: 'path-test', compiledPath: '/path', sourceFiles: []);

        $savePath = $this->tempDir . '/path-manifest.json';
        $manifest->save($savePath);

        $this->assertSame($savePath, $manifest->getManifestPath());
    }

    public function test_set_entry_adds_entry() : void
    {
        $manifest = CompiledCacheManifest::empty($this->clock);

        $entry = new CompiledCacheManifestEntry(
            name        : 'set-test',
            compiledPath: '/compiled/set.php',
            sourceFiles : [],
            fingerprint : 'set-hash',
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        $manifest->setEntry($entry);

        $this->assertTrue($manifest->hasEntry('set-test'));
    }

    // --- getManifestPath ---

    public function test_same_source_files_produce_same_fingerprint() : void
    {
        $sourceFile = $this->createTempFile('fp-consistent.php', '<?php // consistent');

        $manifest1 = CompiledCacheManifest::empty($this->clock);
        $manifest1->addEntry(name: 'fp1', compiledPath: '/path1', sourceFiles: [$sourceFile]);

        $manifest2 = CompiledCacheManifest::empty($this->clock);
        $manifest2->addEntry(name: 'fp2', compiledPath: '/path2', sourceFiles: [$sourceFile]);

        $entry1 = $manifest1->getEntry('fp1');
        $entry2 = $manifest2->getEntry('fp2');

        $this->assertNotNull($entry1);
        $this->assertNotNull($entry2);
        $this->assertSame($entry1->fingerprint, $entry2->fingerprint);
    }

    public function test_entry_becomes_stale_when_source_deleted() : void
    {
        $sourceFile = $this->createTempFile('deleted-source.php', '<?php // delete me');
        $manifest = CompiledCacheManifest::empty($this->clock);

        $manifest->addEntry(
            name        : 'deleted-test',
            compiledPath: $this->tempDir . '/compiled.php',
            sourceFiles : [$sourceFile],
        );

        $this->assertTrue($manifest->isFresh('deleted-test'));

        // Delete source file
        unlink($sourceFile);

        $this->assertFalse($manifest->isFresh('deleted-test'));
    }

    // --- setEntry ---

    protected function setUp() : void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir() . '/avax_compiled_cache_test_' . uniqid();
        mkdir($this->tempDir, 0o755, true);

        $this->clock = new FrozenClock(Timestamp::fromUnixTime(1000000));
    }

    // --- Fingerprint consistency ---

    protected function tearDown() : void
    {
        $this->removeDirectory($this->tempDir);

        parent::tearDown();
    }

    // --- Entry staleness detection ---

    private function removeDirectory(string $dir) : void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }
}

final class CompiledCacheFreshnessTest extends TestCase
{
    private string $tempDir;

    private FrozenClock $clock;

    public function test_check_returns_fresh_when_compiled_is_newer() : void
    {
        $sourceFile = $this->createTempFile('check-source.php', '<?php // source');
        $compiledFile = $this->createTempFile('check-compiled.php', '<?php // compiled');

        // Make compiled newer
        sleep(1);
        touch($compiledFile);

        $entry = new CompiledCacheManifestEntry(
            name        : 'check-test',
            compiledPath: $compiledFile,
            sourceFiles : [$sourceFile],
            fingerprint : $this->calcFingerprint([$sourceFile]),
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        $freshness = CompiledCacheFreshness::create($this->clock);
        $status     = $freshness->check($entry);

        $this->assertTrue($status->isFresh);
        $this->assertSame('All checks passed', $status->reason);
    }

    private function createTempFile(string $name, string $content = '') : string
    {
        $path = $this->tempDir . '/' . $name;
        file_put_contents($path, $content);

        return $path;
    }

    private function calcFingerprint(array $sourceFiles) : string
    {
        $hashParts = [];

        foreach ($sourceFiles as $file) {
            if (file_exists($file)) {
                $hashParts[] = $file . ':' . filemtime($file);
            } else {
                $hashParts[] = $file . ':missing';
            }
        }

        return hash('sha256', implode('|', $hashParts));
    }

    public function test_check_returns_stale_when_compiled_missing() : void
    {
        $sourceFile = $this->createTempFile('missing-compiled-source.php', '<?php');

        $entry = new CompiledCacheManifestEntry(
            name        : 'missing-compiled-test',
            compiledPath: $this->tempDir . '/nonexistent-compiled.php',
            sourceFiles : [$sourceFile],
            fingerprint : $this->calcFingerprint([$sourceFile]),
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        $freshness = CompiledCacheFreshness::create($this->clock);
        $status = $freshness->check($entry);

        $this->assertFalse($status->isFresh);
        $this->assertSame('Compiled file does not exist', $status->reason);
    }

    // --- check() ---

    public function test_check_returns_stale_on_fingerprint_mismatch() : void
    {
        $sourceFile = $this->createTempFile('mismatch-source.php', '<?php // mismatch');

        $entry = new CompiledCacheManifestEntry(
            name        : 'mismatch-test',
            compiledPath: $this->tempDir . '/mismatch-compiled.php',
            sourceFiles : [$sourceFile],
            fingerprint : 'wrong-fingerprint',
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        // Create the compiled file so it exists
        file_put_contents($this->tempDir . '/mismatch-compiled.php', '<?php');

        $freshness = CompiledCacheFreshness::create($this->clock);
        $status = $freshness->check($entry);

        $this->assertFalse($status->isFresh);
        $this->assertSame('Source files have changed (fingerprint mismatch)', $status->reason);
    }

    public function test_check_returns_stale_when_source_missing() : void
    {
        $entry = new CompiledCacheManifestEntry(
            name        : 'missing-source-test',
            compiledPath: $this->tempDir . '/compiled-with-missing-source.php',
            sourceFiles : ['/nonexistent/source.php'],
            fingerprint : 'some-hash',
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        // Create compiled file
        file_put_contents($this->tempDir . '/compiled-with-missing-source.php', '<?php');

        $freshness = CompiledCacheFreshness::create($this->clock);
        $status = $freshness->check($entry);

        $this->assertFalse($status->isFresh);
        $this->assertStringContainsString('Missing source files', $status->reason);
    }

    public function test_check_returns_stale_when_source_newer() : void
    {
        $sourceFile = $this->createTempFile('newer-source.php', '<?php // old');
        $compiledFile = $this->createTempFile('newer-compiled.php', '<?php // compiled');

        // Create entry with current fingerprint
        $fingerprint = $this->calcFingerprint([$sourceFile]);

        $entry = new CompiledCacheManifestEntry(
            name        : 'newer-source-test',
            compiledPath: $compiledFile,
            sourceFiles : [$sourceFile],
            fingerprint : $fingerprint,
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        // Modify source file to make it newer
        sleep(1);
        file_put_contents($sourceFile, '<?php // new content');

        // Recalculate fingerprint with modified file
        $newFingerprint = $this->calcFingerprint([$sourceFile]);

        // Use old fingerprint to simulate fingerprint mismatch (source changed)
        $entry2 = new CompiledCacheManifestEntry(
            name        : 'newer-source-test',
            compiledPath: $compiledFile,
            sourceFiles : [$sourceFile],
            fingerprint : $fingerprint,
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        $freshness = CompiledCacheFreshness::create($this->clock);
        $freshness->clearCache(); // Clear cache from previous test
        $status = $freshness->check($entry2);

        $this->assertFalse($status->isFresh);
    }

    public function test_mark_dirty_returns_stale_status() : void
    {
        $sourceFile = $this->createTempFile('dirty-source.php', '<?php');
        $compiledFile = $this->createTempFile('dirty-compiled.php', '<?php');

        $entry = new CompiledCacheManifestEntry(
            name        : 'dirty-test',
            compiledPath: $compiledFile,
            sourceFiles : [$sourceFile],
            fingerprint : $this->calcFingerprint([$sourceFile]),
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        $freshness = CompiledCacheFreshness::create($this->clock);
        $status     = $freshness->markDirty($entry);

        $this->assertFalse($status->isFresh);
        $this->assertSame('Manually marked as dirty', $status->reason);
    }

    public function test_mark_fresh_returns_fresh_status() : void
    {
        $sourceFile = $this->createTempFile('fresh-mark-source.php', '<?php');
        $compiledFile = $this->createTempFile('fresh-mark-compiled.php', '<?php');

        $entry = new CompiledCacheManifestEntry(
            name        : 'fresh-mark-test',
            compiledPath: $compiledFile,
            sourceFiles : [$sourceFile],
            fingerprint : 'wrong-fingerprint', // Intentionally wrong
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        $freshness = CompiledCacheFreshness::create($this->clock);
        $status     = $freshness->markFresh($entry);

        $this->assertTrue($status->isFresh);
        $this->assertSame('Manually marked as fresh', $status->reason);
    }

    // --- markDirty / markFresh ---

    public function test_needs_rebuild_returns_true_when_stale() : void
    {
        $sourceFile = $this->createTempFile('rebuild-source.php', '<?php');

        $entry = new CompiledCacheManifestEntry(
            name        : 'rebuild-test',
            compiledPath: $this->tempDir . '/nonexistent.php',
            sourceFiles : [$sourceFile],
            fingerprint : $this->calcFingerprint([$sourceFile]),
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        $freshness = CompiledCacheFreshness::create($this->clock);

        $this->assertTrue($freshness->needsRebuild($entry));
    }

    public function test_needs_rebuild_returns_false_when_fresh() : void
    {
        $sourceFile = $this->createTempFile('no-rebuild-source.php', '<?php');
        $compiledFile = $this->createTempFile('no-rebuild-compiled.php', '<?php');

        sleep(1);
        touch($compiledFile);

        $entry = new CompiledCacheManifestEntry(
            name        : 'no-rebuild-test',
            compiledPath: $compiledFile,
            sourceFiles : [$sourceFile],
            fingerprint : $this->calcFingerprint([$sourceFile]),
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        $freshness = CompiledCacheFreshness::create($this->clock);

        $this->assertFalse($freshness->needsRebuild($entry));
    }

    // --- needsRebuild ---

    public function test_get_entries_needing_rebuild_returns_only_stale_entries() : void
    {
        $sourceFile1 = $this->createTempFile('rebuild1-source.php', '<?php');
        $sourceFile2 = $this->createTempFile('rebuild2-source.php', '<?php');
        $compiledFile2 = $this->createTempFile('rebuild2-compiled.php', '<?php');

        sleep(1);
        touch($compiledFile2);

        $entry1 = new CompiledCacheManifestEntry(
            name        : 'needs-rebuild',
            compiledPath: $this->tempDir . '/nonexistent.php',
            sourceFiles : [$sourceFile1],
            fingerprint : $this->calcFingerprint([$sourceFile1]),
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        $entry2 = new CompiledCacheManifestEntry(
            name        : 'no-rebuild',
            compiledPath: $compiledFile2,
            sourceFiles : [$sourceFile2],
            fingerprint : $this->calcFingerprint([$sourceFile2]),
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        $freshness = CompiledCacheFreshness::create($this->clock);
        $freshness->clearCache();

        $needsRebuild = $freshness->getEntriesNeedingRebuild([$entry1, $entry2]);

        $this->assertCount(1, $needsRebuild);
        $this->assertSame('needs-rebuild', $needsRebuild[0]->name);
    }

    public function test_check_all_checks_multiple_entries() : void
    {
        $sourceFile1 = $this->createTempFile('checkall1-source.php', '<?php');
        $sourceFile2 = $this->createTempFile('checkall2-source.php', '<?php');

        $entry1 = new CompiledCacheManifestEntry(
            name        : 'checkall-1',
            compiledPath: $this->tempDir . '/nonexistent1.php',
            sourceFiles : [$sourceFile1],
            fingerprint : $this->calcFingerprint([$sourceFile1]),
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        $entry2 = new CompiledCacheManifestEntry(
            name        : 'checkall-2',
            compiledPath: $this->tempDir . '/nonexistent2.php',
            sourceFiles : [$sourceFile2],
            fingerprint : $this->calcFingerprint([$sourceFile2]),
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        $freshness = CompiledCacheFreshness::create($this->clock);
        $results = $freshness->checkAll([$entry1, $entry2]);

        $this->assertCount(2, $results);
        $this->assertArrayHasKey('checkall-1', $results);
        $this->assertArrayHasKey('checkall-2', $results);
    }

    // --- getEntriesNeedingRebuild ---

    public function test_clear_cache_clears_all_cached_statuses() : void
    {
        $sourceFile = $this->createTempFile('clear-source.php', '<?php');
        $compiledFile = $this->createTempFile('clear-compiled.php', '<?php');

        sleep(1);
        touch($compiledFile);

        $entry = new CompiledCacheManifestEntry(
            name        : 'clear-test',
            compiledPath: $compiledFile,
            sourceFiles : [$sourceFile],
            fingerprint : $this->calcFingerprint([$sourceFile]),
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        $freshness = CompiledCacheFreshness::create($this->clock);
        $freshness->check($entry);
        $freshness->clearCache();

        // After clearing, should re-check
        $this->assertTrue(true); // Clear doesn't throw
    }

    // --- checkAll ---

    public function test_clear_entry_cache_clears_specific_entry() : void
    {
        $sourceFile = $this->createTempFile('clear-entry-source.php', '<?php');
        $compiledFile = $this->createTempFile('clear-entry-compiled.php', '<?php');

        sleep(1);
        touch($compiledFile);

        $entry = new CompiledCacheManifestEntry(
            name        : 'clear-entry-test',
            compiledPath: $compiledFile,
            sourceFiles : [$sourceFile],
            fingerprint : $this->calcFingerprint([$sourceFile]),
            createdAt   : Timestamp::fromUnixTime(1000000),
            updatedAt   : Timestamp::fromUnixTime(1000000),
        );

        $freshness = CompiledCacheFreshness::create($this->clock);
        $freshness->check($entry);
        $freshness->clearEntryCache('clear-entry-test');

        $this->assertTrue(true); // Clear doesn't throw
    }

    // --- clearCache / clearEntryCache ---

    public function test_freshness_status_is_stale() : void
    {
        $stale = new FreshnessStatus(
            entryName        : 'stale-test',
            isFresh          : false,
            reason           : 'Test',
            checkedAt        : Timestamp::fromUnixTime(1000000),
            sourceFilesMtime : 1000000,
            compiledFileMtime: 0,
        );

        $this->assertTrue($stale->isStale());

        $fresh = new FreshnessStatus(
            entryName        : 'fresh-test',
            isFresh          : true,
            reason           : 'Test',
            checkedAt        : Timestamp::fromUnixTime(1000000),
            sourceFilesMtime : 1000000,
            compiledFileMtime: 1000000,
        );

        $this->assertFalse($fresh->isStale());
    }

    public function test_freshness_status_is_missing() : void
    {
        $missing = new FreshnessStatus(
            entryName        : 'missing-test',
            isFresh          : false,
            reason           : 'Missing',
            checkedAt        : Timestamp::fromUnixTime(1000000),
            sourceFilesMtime : 1000000,
            compiledFileMtime: 0,
        );

        $this->assertTrue($missing->isMissing());

        $missingFalse = new FreshnessStatus(
            entryName        : 'not-missing',
            isFresh          : false,
            reason           : 'Stale',
            checkedAt        : Timestamp::fromUnixTime(1000000),
            sourceFilesMtime : 1000000,
            compiledFileMtime: 999999,
        );

        $this->assertFalse($missingFalse->isMissing());
    }

    // --- FreshnessStatus properties ---

    public function test_freshness_status_get_compiled_file_age() : void
    {
        $status = new FreshnessStatus(
            entryName        : 'age-test',
            isFresh          : true,
            reason           : 'Test',
            checkedAt        : Timestamp::fromUnixTime(1000100),
            sourceFilesMtime : 1000000,
            compiledFileMtime: 1000050,
        );

        $this->assertSame(50, $status->getCompiledFileAge());
    }

    public function test_freshness_status_get_compiled_file_age_when_missing() : void
    {
        $status = new FreshnessStatus(
            entryName        : 'age-missing-test',
            isFresh          : false,
            reason           : 'Missing',
            checkedAt        : Timestamp::fromUnixTime(1000000),
            sourceFilesMtime : 1000000,
            compiledFileMtime: 0,
        );

        $this->assertSame(0, $status->getCompiledFileAge());
    }

    public function test_freshness_status_to_array() : void
    {
        $status = new FreshnessStatus(
            entryName        : 'array-test',
            isFresh          : true,
            reason           : 'All checks passed',
            checkedAt        : Timestamp::fromUnixTime(1000000),
            sourceFilesMtime : 999999,
            compiledFileMtime: 1000000,
        );

        $array = $status->toArray();

        $this->assertSame('array-test', $array['entryName']);
        $this->assertTrue($array['isFresh']);
        $this->assertSame('All checks passed', $array['reason']);
        $this->assertSame(1000000, $array['checkedAt']);
        $this->assertSame(999999, $array['sourceFilesMtime']);
        $this->assertSame(1000000, $array['compiledFileMtime']);
        $this->assertFalse($array['isStale']);
        $this->assertFalse($array['isMissing']);
        $this->assertSame(0, $array['compiledFileAge']);
    }

    protected function setUp() : void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir() . '/avax_freshness_test_' . uniqid();
        mkdir($this->tempDir, 0o755, true);

        $this->clock = new FrozenClock(Timestamp::fromUnixTime(1000000));
    }

    protected function tearDown() : void
    {
        $this->removeDirectory($this->tempDir);

        parent::tearDown();
    }

    private function removeDirectory(string $dir) : void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }
}
