<?php

declare(strict_types=1);

/**
 * PHP Type Mapper Usage Examples
 *
 * Demonstrates how to use SQLToPHPTypeMapper for DTO/Entity generation.
 */

use Avax\Migrations\TypeMapping\SQLToPHPTypeMapper;

require_once __DIR__ . '/../TypeMapping/SQLToPHPTypeMapper.php';

$mapper = new SQLToPHPTypeMapper();

echo "=== SQL to PHP Type Mapping Examples ===\n\n";

// ========================================
// BASIC TYPE MAPPING
// ========================================

echo "1. Basic Type Mapping:\n";
echo "   VARCHAR(255) → " . $mapper->toPhpType('VARCHAR(255)') . "\n";
echo "   BIGINT → " . $mapper->toPhpType('BIGINT') . "\n";
echo "   DECIMAL(10,2) → " . $mapper->toPhpType('DECIMAL(10,2)') . "\n";
echo "   TIMESTAMP → " . $mapper->toPhpType('TIMESTAMP') . "\n";
echo "   JSON → " . $mapper->toPhpType('JSON') . "\n";
echo "   BOOLEAN → " . $mapper->toPhpType('BOOLEAN') . "\n\n";

// ========================================
// PHPDOC TYPE HINTS
// ========================================

echo "2. PHPDoc Type Hints:\n";
echo "   JSON (nullable) → " . $mapper->toDocBlockType('JSON', nullable: true) . "\n";
echo "   POINT → " . $mapper->toDocBlockType('POINT') . "\n";
echo "   SET → " . $mapper->toDocBlockType('SET') . "\n";
echo "   BIGINT (nullable) → " . $mapper->toDocBlockType('BIGINT', nullable: true) . "\n\n";

// ========================================
// VALUE OBJECT SUGGESTIONS
// ========================================

echo "3. Value Object Suggestions:\n";
$types = ['UUID', 'INET', 'MONEY', 'POINT', 'VARCHAR'];
foreach ($types as $type) {
    $shouldUse = $mapper->shouldUseValueObject($type) ? 'YES' : 'NO';
    $vo        = $mapper->suggestValueObject($type) ?? 'N/A';
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
    $phpType = $mapper->toPhpType($column['type']);
    $docType = $mapper->toDocBlockType($column['type'], $column['nullable']);
    $vo      = $mapper->suggestValueObject($column['type']);

    // Use Value Object if suggested
    if ($vo !== null) {
        $phpType = $vo;
    }

    // Add nullable prefix
    $typeHint = $column['nullable'] ? "?{$phpType}" : $phpType;

    // Add PHPDoc for complex types
    if (in_array($phpType, ['array', 'GeoPoint'], true)) {
        echo "    /** @var {$docType} */\n";
    }

    echo "    public {$typeHint} \${$column['name']};\n\n";
}

echo "}\n\n";

// ========================================
// SUPPORTED TYPES LIST
// ========================================

echo "5. All Supported Types (" . count($mapper->getSupportedTypes()) . " total):\n";
$types  = $mapper->getSupportedTypes();
$chunks = array_chunk($types, 5);
foreach ($chunks as $chunk) {
    echo "   " . implode(', ', $chunk) . "\n";
}
echo "\n";

// ========================================
// TYPE VALIDATION
// ========================================

echo "6. Type Validation:\n";
$testTypes = ['VARCHAR', 'BIGINT', 'FOOBAR', 'JSON', 'INVALID'];
foreach ($testTypes as $type) {
    $isSupported = $mapper->isSupported($type) ? '✓ Supported' : '✗ Not Supported';
    echo "   {$type}: {$isSupported}\n";
}
echo "\n";

echo "=== End of Examples ===\n";
