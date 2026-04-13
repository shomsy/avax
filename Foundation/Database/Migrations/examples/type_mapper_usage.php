<?php

declare(strict_types=1);

/**
 * PHP Type Mapper Usage Examples
 *
 * Demonstrates how to use SQLToPHPTypeMapper for DTO/Entity generation.
 */

use Avax\Migrations\Design\TypeMapping\SQLToPHPTypeMapper;

require_once __DIR__ . '/../Design/TypeMapping/SQLToPHPTypeMapper.php';

$mapper = new SQLToPHPTypeMapper;

echo "=== SQL to PHP Type Mapping Examples ===\n\n";

// ========================================
// BASIC TYPE MAPPING
// ========================================

echo "1. Basic Type Mapping:\n";
echo '   VARCHAR(255) → ' . $mapper->toPhpType(sqlType: 'VARCHAR(255)') . "\n";
echo '   BIGINT → ' . $mapper->toPhpType(sqlType: 'BIGINT') . "\n";
echo '   DECIMAL(10,2) → ' . $mapper->toPhpType(sqlType: 'DECIMAL(10,2)') . "\n";
echo '   TIMESTAMP → ' . $mapper->toPhpType(sqlType: 'TIMESTAMP') . "\n";
echo '   JSON → ' . $mapper->toPhpType(sqlType: 'JSON') . "\n";
echo '   BOOLEAN → ' . $mapper->toPhpType(sqlType: 'BOOLEAN') . "\n\n";

// ========================================
// PHPDOC TYPE HINTS
// ========================================

echo "2. PHPDoc Type Hints:\n";
echo '   JSON (nullable) → ' . $mapper->toDocBlockType(sqlType: 'JSON', nullable: true) . "\n";
echo '   POINT → ' . $mapper->toDocBlockType(sqlType: 'POINT') . "\n";
echo '   SET → ' . $mapper->toDocBlockType(sqlType: 'SET') . "\n";
echo '   BIGINT (nullable) → ' . $mapper->toDocBlockType(sqlType: 'BIGINT', nullable: true) . "\n\n";

// ========================================
// VALUE OBJECT SUGGESTIONS
// ========================================

echo "3. Value Object Suggestions:\n";
$types = ['UUID', 'INET', 'MONEY', 'POINT', 'VARCHAR'];
foreach ($types as $type) {
    $shouldUse = $mapper->shouldUseValueObject(sqlType: $type) ? 'YES' : 'NO';
    $vo        = $mapper->suggestValueObject(sqlType: $type) ?? 'N/A';
    echo "   {$type}: Use VO? {$shouldUse}, Suggested: {$vo}\n";
}
echo "\n";

// ========================================
// DTO GENERATION EXAMPLE
// ========================================

echo "4. Generated DTO Example:\n\n";

$columns = [
    ['name' => 'id', 'type' => 'BIGINT', 'nullable' => false],
    ['name' => 'email', 'type' => 'VARCHAR(255)', 'nullable' => false],
    ['name' => 'age', 'type' => 'INT', 'nullable' => true],
    ['name' => 'price', 'type' => 'DECIMAL(10,2)', 'nullable' => false],
    ['name' => 'created_at', 'type' => 'TIMESTAMP', 'nullable' => false],
    ['name' => 'metadata', 'type' => 'JSON', 'nullable' => true],
    ['name' => 'location', 'type' => 'POINT', 'nullable' => false],
    ['name' => 'external_id', 'type' => 'UUID', 'nullable' => false],
];

echo "<?php\n\n";
echo "declare(strict_types=1);\n\n";
echo "final class ProductDTO\n{\n";

foreach ($columns as $column) {
    $phpType = $mapper->toPhpType(sqlType: $column['type']);
    $docType = $mapper->toDocBlockType(sqlType: $column['type'], nullable: $column['nullable']);
    $vo      = $mapper->suggestValueObject(sqlType: $column['type']);

    // Use Value Object if suggested
    if ($vo !== null) {
        $phpType = $vo;
    }

    // Add nullable prefix
    $typeHint = $column['nullable'] ? "?{$phpType}" : $phpType;

    // Add PHPDoc for complex types
    if (in_array(needle: $phpType, haystack: ['array', 'GeoPoint'], strict: true)) {
        echo "    /** @var {$docType} */\n";
    }

    echo "    public {$typeHint} \${$column['name']};\n\n";
}

echo "}\n\n";

// ========================================
// SUPPORTED TYPES LIST
// ========================================

echo '5. All Supported Types (' . count(value: $mapper->getSupportedTypes()) . " total):\n";
$types  = $mapper->getSupportedTypes();
$chunks = array_chunk(array: $types, length: 5);
foreach ($chunks as $chunk) {
    echo '   ' . implode(separator: ', ', array: $chunk) . "\n";
}
echo "\n";

// ========================================
// TYPE VALIDATION
// ========================================

echo "6. Type Validation:\n";
$testTypes = ['VARCHAR', 'BIGINT', 'FOOBAR', 'JSON', 'INVALID'];
foreach ($testTypes as $type) {
    $isSupported = $mapper->isSupported(sqlType: $type) ? '✓ Supported' : '✗ Not Supported';
    echo "   {$type}: {$isSupported}\n";
}
echo "\n";

echo "=== End of Examples ===\n";
