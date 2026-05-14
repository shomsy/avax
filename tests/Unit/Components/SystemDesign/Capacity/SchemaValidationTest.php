<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\SystemDesign\Capacity;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\SystemDesign\System\Capabilities\SchemaValidation\NativeYamlParser;
use Avax\Components\SystemDesign\System\Capabilities\SchemaValidation\SchemaValidationResult;
use Avax\Components\SystemDesign\System\Capabilities\SchemaValidation\SchemaValidator;
use Avax\Components\SystemDesign\System\Flows\ValidateArchitectureTestsSchema\ValidateArchitectureTestsSchema;
use Avax\Components\SystemDesign\System\Flows\ValidateCapacitySchema\ValidateCapacitySchema;
use Avax\Components\SystemDesign\System\Flows\ValidateScenariosSchema\ValidateScenariosSchema;
use Avax\Tests\TestCase;

final class SchemaValidationTest extends TestCase
{
    private string $labsRoot;
    private string $schemasDir;
    private string $examplesDir;

    private function newParser() : NativeYamlParser
    {
        return new NativeYamlParser(filesystem: new Filesystem());
    }

    private function newValidator() : SchemaValidator
    {
        return new SchemaValidator(yamlParser: $this->newParser());
    }

    private function newValidateCapacitySchema() : ValidateCapacitySchema
    {
        return new ValidateCapacitySchema(
            validator: $this->newValidator(),
            schemaDir: $this->schemasDir,
        );
    }

    private function newValidateScenariosSchema() : ValidateScenariosSchema
    {
        return new ValidateScenariosSchema(
            validator: $this->newValidator(),
            schemaDir: $this->schemasDir,
        );
    }

    private function newValidateArchitectureTestsSchema() : ValidateArchitectureTestsSchema
    {
        return new ValidateArchitectureTestsSchema(
            validator: $this->newValidator(),
            schemaDir: $this->schemasDir,
        );
    }

    public function testParserHandlesSimpleKeyValue() : void
    {
        $parser = $this->newParser();
        $result = $parser->parse("name: test\nvalue: 42\n");

        self::assertSame('test', $result['name']);
        self::assertSame(42, $result['value']);
    }

    // --- NativeYamlParser tests ---

    public function testParserHandlesNestedMaps() : void
    {
        $parser = $this->newParser();
        $yaml   = "parent:\n  child: value\n  other: 123\n";
        $result = $parser->parse($yaml);

        self::assertArrayHasKey('parent', $result);
        self::assertSame('value', $result['parent']['child']);
        self::assertSame(123, $result['parent']['other']);
    }

    public function testParserHandlesScalarTypes() : void
    {
        $parser = $this->newParser();
        $yaml   = "string_val: hello\nint_val: 42\nfloat_val: 3.14\nbool_true: true\nbool_false: false\nnull_val: null\n";
        $result = $parser->parse($yaml);

        self::assertSame('hello', $result['string_val']);
        self::assertSame(42, $result['int_val']);
        self::assertSame(3.14, $result['float_val']);
        self::assertTrue($result['bool_true']);
        self::assertFalse($result['bool_false']);
        self::assertNull($result['null_val']);
    }

    public function testParserHandlesSimpleLists() : void
    {
        $parser = $this->newParser();
        $yaml   = "items:\n  - apple\n  - banana\n  - cherry\n";
        $result = $parser->parse($yaml);

        self::assertSame(['apple', 'banana', 'cherry'], $result['items']);
    }

    public function testParserHandlesListsOfMaps() : void
    {
        $parser = $this->newParser();
        $yaml   = "people:\n  - name: Alice\n    age: 30\n  - name: Bob\n    age: 25\n";
        $result = $parser->parse($yaml);

        self::assertCount(2, $result['people']);
        self::assertSame('Alice', $result['people'][0]['name']);
        self::assertSame(30, $result['people'][0]['age']);
        self::assertSame('Bob', $result['people'][1]['name']);
        self::assertSame(25, $result['people'][1]['age']);
    }

    public function testParserHandlesInlineArrays() : void
    {
        $parser = $this->newParser();
        $result = $parser->parse("tags: [one, two, three]\n");

        self::assertSame(['one', 'two', 'three'], $result['tags']);
    }

    public function testParserHandlesQuotedStrings() : void
    {
        $parser = $this->newParser();
        $result = $parser->parse('greeting: "hello: world"');

        self::assertSame('hello: world', $result['greeting']);
    }

    public function testParserStripsComments() : void
    {
        $parser = $this->newParser();
        $result = $parser->parse("key: value # this is a comment\n");

        self::assertSame('value', $result['key']);
    }

    public function testValidatorAcceptsValidCapacity() : void
    {
        $validator = $this->newValidator();
        $result    = $validator->validateFile(
            $this->schemasDir . '/capacity-schema.yaml',
            $this->examplesDir . '/valid-capacity.yaml',
        );

        self::assertTrue($result->valid);
        self::assertSame([], $result->errors);
    }

    // --- SchemaValidator tests ---

    public function testValidatorRejectsBadCapacityValues() : void
    {
        $validator = $this->newValidator();
        $result    = $validator->validateFile(
            $this->schemasDir . '/capacity-schema.yaml',
            $this->examplesDir . '/invalid-capacity-bad-values.yaml',
        );

        self::assertFalse($result->valid);
        self::assertNotEmpty($result->errors);
    }

    public function testValidatorRejectsMissingCapacitySections() : void
    {
        $validator = $this->newValidator();
        $result    = $validator->validateFile(
            $this->schemasDir . '/capacity-schema.yaml',
            $this->examplesDir . '/invalid-capacity-missing-sections.yaml',
        );

        self::assertFalse($result->valid);
        self::assertNotEmpty($result->errors);

        // Should report missing required sections
        $errorText = implode(' ', $result->errors);
        self::assertStringContainsString('queue', $errorText);
        self::assertStringContainsString('latency', $errorText);
        self::assertStringContainsString('availability', $errorText);
    }

    public function testValidatorAcceptsValidScenarios() : void
    {
        $validator = $this->newValidator();
        $result    = $validator->validateFile(
            $this->schemasDir . '/scenarios-schema.yaml',
            $this->examplesDir . '/valid-scenarios.yaml',
        );

        self::assertTrue($result->valid);
        self::assertSame([], $result->errors);
    }

    public function testValidatorRejectsBadScenarios() : void
    {
        $validator = $this->newValidator();
        $result    = $validator->validateFile(
            $this->schemasDir . '/scenarios-schema.yaml',
            $this->examplesDir . '/invalid-scenarios-bad.yaml',
        );

        self::assertFalse($result->valid);
        self::assertNotEmpty($result->errors);
    }

    public function testValidatorAcceptsValidArchitectureTests() : void
    {
        $validator = $this->newValidator();
        $result    = $validator->validateFile(
            $this->schemasDir . '/architecture-tests-schema.yaml',
            $this->examplesDir . '/valid-architecture-tests.yaml',
        );

        self::assertTrue($result->valid);
        self::assertSame([], $result->errors);
    }

    public function testValidatorRejectsBadArchitectureTests() : void
    {
        $validator = $this->newValidator();
        $result    = $validator->validateFile(
            $this->schemasDir . '/architecture-tests-schema.yaml',
            $this->examplesDir . '/invalid-architecture-tests-bad.yaml',
        );

        self::assertFalse($result->valid);
        self::assertNotEmpty($result->errors);
    }

    public function testValidateCapacitySchemaFlowAcceptsValidFile() : void
    {
        $flow = $this->newValidateCapacitySchema();
        $result = $flow->execute($this->examplesDir . '/valid-capacity.yaml');

        self::assertTrue($result->valid);
        self::assertSame([], $result->errors);
    }

    // --- Flow tests ---

    public function testValidateCapacitySchemaFlowRejectsInvalidFile() : void
    {
        $flow = $this->newValidateCapacitySchema();
        $result = $flow->execute($this->examplesDir . '/invalid-capacity-bad-values.yaml');

        self::assertFalse($result->valid);
        self::assertNotEmpty($result->errors);
    }

    public function testValidateScenariosSchemaFlowAcceptsValidFile() : void
    {
        $flow = $this->newValidateScenariosSchema();
        $result = $flow->execute($this->examplesDir . '/valid-scenarios.yaml');

        self::assertTrue($result->valid);
        self::assertSame([], $result->errors);
    }

    public function testValidateScenariosSchemaFlowRejectsInvalidFile() : void
    {
        $flow = $this->newValidateScenariosSchema();
        $result = $flow->execute($this->examplesDir . '/invalid-scenarios-bad.yaml');

        self::assertFalse($result->valid);
        self::assertNotEmpty($result->errors);
    }

    public function testValidateArchitectureTestsSchemaFlowAcceptsValidFile() : void
    {
        $flow = $this->newValidateArchitectureTestsSchema();
        $result = $flow->execute($this->examplesDir . '/valid-architecture-tests.yaml');

        self::assertTrue($result->valid);
        self::assertSame([], $result->errors);
    }

    public function testValidateArchitectureTestsSchemaFlowRejectsInvalidFile() : void
    {
        $flow = $this->newValidateArchitectureTestsSchema();
        $result = $flow->execute($this->examplesDir . '/invalid-architecture-tests-bad.yaml');

        self::assertFalse($result->valid);
        self::assertNotEmpty($result->errors);
    }

    public function testSchemaValidationResultPassFactory() : void
    {
        $result = SchemaValidationResult::pass(
            'test-schema',
            'test-target',
        );

        self::assertTrue($result->valid);
        self::assertSame([], $result->errors);
        self::assertSame('test-schema', $result->schema);
        self::assertSame('test-target', $result->target);
    }

    // --- SchemaValidationResult tests ---

    public function testSchemaValidationResultFailFactory() : void
    {
        $result = SchemaValidationResult::fail(
            ['error one', 'error two'],
            'test-schema',
            'test-target',
        );

        self::assertFalse($result->valid);
        self::assertSame(['error one', 'error two'], $result->errors);
        self::assertSame('test-schema', $result->schema);
        self::assertSame('test-target', $result->target);
    }

    protected function setUp() : void
    {
        parent::setUp();
        $this->labsRoot    = dirname(__DIR__, 5) . '/components/SystemDesign';
        $this->schemasDir  = $this->labsRoot . '/schemas';
        $this->examplesDir = $this->labsRoot . '/examples';
    }
}
