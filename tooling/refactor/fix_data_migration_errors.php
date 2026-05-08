#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Fix migration-induced named argument mismatches in DataStack/Data.
 *
 * The migration subagent renamed method parameters but did not update
 * all named argument call sites. This script fixes them.
 *
 * Run with: php tooling/refactor/fix_data_migration_errors.php
 */

$base = __DIR__ . '/../../components/DataStack/Data/System';

$fixes = [
    // ── DataTransferResult.php ──
    [
        'file' => $base . '/Capabilities/Coercion/DtoSystem/DataTransferResult.php',
        'search' => 'failure: null',
        'replace' => 'dataTransferFailure: null',
    ],
    [
        'file' => $base . '/Capabilities/Coercion/DtoSystem/DataTransferResult.php',
        'search' => 'failure: $dataTransferFailure',
        'replace' => 'dataTransferFailure: $dataTransferFailure',
    ],

    // ── DataTransfer.php ──
    [
        'file' => $base . '/Capabilities/Coercion/DtoSystem/DataTransfer.php',
        'search' => 'failure(failure: $failure)',
        'replace' => 'failure(dataTransferFailure: $failure)',
    ],
    [
        'file' => $base . '/Capabilities/Coercion/DtoSystem/DataTransfer.php',
        'search' => "failure:\n                    failure: new DataTransferFailure(",
        'replace' => "failure:\n                dataTransferFailure: new DataTransferFailure(",
    ],

    // ── InspectDataShape.php ──
    [
        'file' => $base . '/Capabilities/Shapes/ClassShape/InspectDataShape.php',
        'search' => 'config: $config',
        'replace' => 'dataTransferConfig: $config',
    ],

    // ── ReadClassDataShape.php ──
    [
        'file' => $base . '/Capabilities/Shapes/ClassShape/ReadClassDataShape.php',
        'search' => 'class: $reflectionClass, config: $dataTransferConfig',
        'replace' => 'reflectionClass: $reflectionClass, dataTransferConfig: $dataTransferConfig',
    ],
    [
        'file' => $base . '/Capabilities/Shapes/ClassShape/ReadClassDataShape.php',
        'search' => "class         : \$reflectionClass,\n            config        : \$dataTransferConfig",
        'replace' => "reflectionClass     : \$reflectionClass,\n            dataTransferConfig: \$dataTransferConfig",
    ],

    // ── ReadConstructorDataFields.php ──
    [
        'file' => $base . '/Capabilities/Shapes/ClassShape/ReadConstructorDataFields.php',
        'search' => 'property: $property, parameter: $reflectionParameter',
        'replace' => 'reflectionProperty: $property, reflectionParameter: $reflectionParameter',
    ],
    [
        'file' => $base . '/Capabilities/Shapes/ClassShape/ReadConstructorDataFields.php',
        'search' => "config    : \$dataTransferConfig",
        'replace' => "dataTransferConfig: \$dataTransferConfig",
    ],
    [
        'file' => $base . '/Capabilities/Shapes/ClassShape/ReadConstructorDataFields.php',
        'search' => "type              : DataFieldType::fromReflectionType(type:",
        'replace' => "dataFieldType     : DataFieldType::fromReflectionType(reflectionType:",
    ],
    [
        'file' => $base . '/Capabilities/Shapes/ClassShape/ReadConstructorDataFields.php',
        'search' => "property          : \$property,",
        'replace' => "reflectionProperty: \$property,",
    ],
    [
        'file' => $base . '/Capabilities/Shapes/ClassShape/ReadConstructorDataFields.php',
        'search' => "parameter         : \$reflectionParameter,",
        'replace' => "reflectionParameter: \$reflectionParameter,",
    ],

    // ── ReadPublicDataFields.php ──
    [
        'file' => $base . '/Capabilities/Shapes/ClassShape/ReadPublicDataFields.php',
        'search' => 'property: $reflectionProperty',
        'replace' => 'reflectionProperty: $reflectionProperty',
    ],
    [
        'file' => $base . '/Capabilities/Shapes/ClassShape/ReadPublicDataFields.php',
        'search' => "config    : \$dataTransferConfig",
        'replace' => "dataTransferConfig: \$dataTransferConfig",
    ],
    [
        'file' => $base . '/Capabilities/Shapes/ClassShape/ReadPublicDataFields.php',
        'search' => "type              : DataFieldType::fromReflectionType(type:",
        'replace' => "dataFieldType     : DataFieldType::fromReflectionType(reflectionType:",
    ],
    [
        'file' => $base . '/Capabilities/Shapes/ClassShape/ReadPublicDataFields.php',
        'search' => "property          : \$reflectionProperty,",
        'replace' => "reflectionProperty: \$reflectionProperty,",
    ],

    // ── ReadVisibleFields.php ──
    [
        'file' => $base . '/Capabilities/Shapes/Visibility/ReadVisibleFields.php',
        'search' => "field        : \$dataField",
        'replace' => "dataField    : \$dataField",
    ],

    // ── ShouldExposeField.php ──
    [
        'file' => $base . '/Capabilities/Shapes/Visibility/ShouldExposeField.php',
        'search' => 'shouldHide(field:',
        'replace' => 'shouldHide(dataField:',
    ],

    // ── ReadDataObject.php (already fixed if owned by user, but ensure consistency) ──
    [
        'file' => $base . '/../../Flows/ReadDataObject/ReadDataObject.php',
        'search' => 'config: $config',
        'replace' => 'dataTransferConfig: $config',
    ],
    [
        'file' => $base . '/../../Flows/ReadDataObject/ReadDataObject.php',
        'search' => 'shape: $dataShape',
        'replace' => 'dataShape: $dataShape',
    ],

    // ── ReadVisibleDataFields.php ──
    [
        'file' => $base . '/../../Flows/ReadDataObject/ReadVisibleDataFields.php',
        'search' => 'shape: $dataShape',
        'replace' => 'dataShape: $dataShape',
    ],

    // ── ReadDataObjectValues.php ──
    [
        'file' => $base . '/../../Flows/ReadDataObject/ReadDataObjectValues.php',
        'search' => '$field->property',
        'replace' => '$field->reflectionProperty',
    ],

    // ── SerializeLegacyDTO.php ──
    [
        'file' => $base . '/Capabilities/Coercion/DtoSystem/SerializeLegacyDTO.php',
        'search' => 'config: DataTransferConfig::legacy()',
        'replace' => 'dataTransferConfig: DataTransferConfig::legacy()',
    ],
    [
        'file' => $base . '/Capabilities/Coercion/DtoSystem/SerializeLegacyDTO.php',
        'search' => 'return collect(items: $this->toArray(object: $object));',
        'replace' => "return new \\Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Forms\\CollectionForm\\Collection(\$this->toArray(object: \$object));",
    ],

    // ── Moment.php ──
    [
        'file' => $base . '/Capabilities/Values/Temporal/Moment.php',
        'search' => "if (\$timezone instanceof string) {\n            \$timezone = new DateTimeZone(\$timezone);\n        }",
        'replace' => "\$tz = \$timezone instanceof \\DateTimeZone ? \$timezone : new \\DateTimeZone((string) \$timezone);",
    ],
    [
        'file' => $base . '/Capabilities/Values/Temporal/Moment.php',
        'search' => "if (\$time instanceof DateTimeImmutable) {\n            \$this->dateTimeImmutable = \$timezone ? \$time->setTimezone(\$timezone) : \$time;",
        'replace' => "if (\$time instanceof DateTimeImmutable) {\n            \$this->dateTimeImmutable = \$time->setTimezone(\$tz);",
    ],
    [
        'file' => $base . '/Capabilities/Values/Temporal/Moment.php',
        'search' => '$this->dateTimeImmutable = new DateTimeImmutable($time, $timezone);',
        'replace' => '$this->dateTimeImmutable = new DateTimeImmutable($time, $tz);',
    ],
    [
        'file' => $base . '/Capabilities/Values/Temporal/Moment.php',
        'search' => "public function __construct(string|DateTimeImmutable \$time = 'now', string|DateTimeZone|null \$timezone = null)",
        'replace' => "public function __construct(string|DateTimeImmutable \$time = 'now', \\DateTimeZone|string|null \$timezone = null)",
    ],
    [
        'file' => $base . '/Capabilities/Values/Temporal/Moment.php',
        'search' => "public static function today(string|DateTimeZone|null \$timezone = null): self",
        'replace' => "public static function today(\\DateTimeZone|string|null \$timezone = null): self",
    ],
    [
        'file' => $base . '/Capabilities/Values/Temporal/Moment.php',
        'search' => "public static function now(string|DateTimeZone|null \$timezone = null): self",
        'replace' => "public static function now(\\DateTimeZone|string|null \$timezone = null): self",
    ],

    // ── DataFieldType.php ──
    [
        'file' => $base . '/Capabilities/Shapes/ClassShape/DataFieldType.php',
        'search' => "names     : \$names === [] ? ['mixed'] : array_values(array: \$names),",
        'replace' => "names     : \$names === [] ? ['mixed'] : \$names,",
    ],
];

$applied = 0;
$skipped = 0;
$errors = 0;

foreach ($fixes as $i => $fix) {
    $file = $fix['file'];

    if (!file_exists($file)) {
        echo "SKIP (not found): {$file}\n";
        $skipped++;
        continue;
    }

    if (!is_writable($file)) {
        echo "SKIP (not writable): {$file}\n";
        $skipped++;
        continue;
    }

    $content = file_get_contents($file);
    $search = $fix['search'];
    $replace = $fix['replace'];

    if (str_contains($content, $search)) {
        $content = str_replace($search, $replace, $content);
        file_put_contents($file, $content);
        echo "FIXED [{$i}]: {$file}\n";
        $applied++;
    } else {
        // Already fixed or pattern changed
        echo "SKIP (pattern not found): {$file} => {$search}\n";
        $skipped++;
    }
}

echo "\nDone: {$applied} applied, {$skipped} skipped, {$errors} errors.\n";
