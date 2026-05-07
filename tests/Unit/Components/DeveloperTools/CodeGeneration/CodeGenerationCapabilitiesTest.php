<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DeveloperTools\CodeGeneration;

use Avax\Components\DeveloperTools\CodeGeneration\System\Capabilities\Generators\EntityGenerator;
use PHPUnit\Framework\TestCase;

final class CodeGenerationCapabilitiesTest extends TestCase
{
    private string $tempDir;

    public function test_entity_generator_produces_code() : void
    {
        $generator = new EntityGenerator(
            baseDirectory   : $this->tempDir,
            defaultNamespace: 'Avax\Generated'
        );

        $path = $generator->generate('User', [
            'table'  => 'users',
            'fields' => [
                ['name' => 'email', 'type' => 'string'],
                ['name' => 'age', 'type' => 'int']
            ]
        ]);

        $this->assertFileExists($path);
        $content = file_get_contents($path);
        $this->assertIsString($content);

        $this->assertStringContainsString('namespace Avax\Generated\Entities;', $content);
        $this->assertStringContainsString('class UserEntity', $content);
        $this->assertStringContainsString('private string $email;', $content);
        $this->assertStringContainsString('private int $age;', $content);
        $this->assertStringContainsString('public function getEmail(): string', $content);
        $this->assertStringContainsString('public function getAge(): int', $content);
    }

    protected function setUp() : void
    {
        $this->tempDir = sys_get_temp_dir() . '/avax_codegen_test_' . uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown() : void
    {
        $this->removeDirectory($this->tempDir);
    }

    private function removeDirectory(string $dir) : void
    {
        if (! is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->removeDirectory("$dir/$file") : unlink("$dir/$file");
        }
        rmdir($dir);
    }
}
