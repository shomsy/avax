<?php

declare(strict_types=1);

/**
 * Migration script: Extract DataTransfer from DataStack/Data into its own component.
 *
 * Moves 46 files, rewrites namespaces/imports, deletes old directories.
 */

$baseDir      = __DIR__ . '/../..';
$dataBase     = $baseDir . '/components/DataStack/Data/System';
$transferBase = $baseDir . '/components/DataStack/DataTransfer/System';

// ── File mapping: [old relative path, new relative path, namespace replacements] ──

$oldNs = 'Avax\\Components\\DataStack\\Data\\System';
$newNs = 'Avax\\Components\\DataStack\\DataTransfer\\System';

$files = [
    // Group A: Attributes → Capabilities/AttributeReading
    ['Capabilities/Coercion/DtoSystem/Attributes/CastWith.php', 'Capabilities/AttributeReading/CastWith.php'],
    ['Capabilities/Coercion/DtoSystem/Attributes/DefaultValue.php', 'Capabilities/AttributeReading/DefaultValue.php'],
    ['Capabilities/Coercion/DtoSystem/Attributes/Hidden.php', 'Capabilities/AttributeReading/Hidden.php'],
    ['Capabilities/Coercion/DtoSystem/Attributes/ListOf.php', 'Capabilities/AttributeReading/ListOf.php'],
    ['Capabilities/Coercion/DtoSystem/Attributes/MapFrom.php', 'Capabilities/AttributeReading/MapFrom.php'],
    ['Capabilities/Coercion/DtoSystem/Attributes/Optional.php', 'Capabilities/AttributeReading/Optional.php'],
    ['Capabilities/Coercion/DtoSystem/Attributes/Required.php', 'Capabilities/AttributeReading/Required.php'],

    // Group B: Configuration → Configuration/
    ['Capabilities/Coercion/DtoSystem/Configuration/DataTransferConfig.php', 'Configuration/DataTransferConfig.php'],
    ['Capabilities/Coercion/DtoSystem/Configuration/UnknownFieldPolicy.php', 'Configuration/UnknownFieldPolicy.php'],

    // Group C: ValueConversion → Capabilities/ValueConversion
    ['Capabilities/Coercion/DtoSystem/ValueConversion/ValueCasterInterface.php', 'Capabilities/ValueConversion/ValueCasterInterface.php'],
    ['Capabilities/Coercion/DtoSystem/ValueConversion/ValueConversionContext.php', 'Capabilities/ValueConversion/ValueConversionContext.php'],

    // Group D: DataShape + Visibility → Capabilities/DataShapeInspection
    ['Capabilities/Shapes/ClassShape/DataShape.php', 'Capabilities/DataShapeInspection/DataShape.php'],
    ['Capabilities/Shapes/ClassShape/DataField.php', 'Capabilities/DataShapeInspection/DataField.php'],
    ['Capabilities/Shapes/ClassShape/DataFieldType.php', 'Capabilities/DataShapeInspection/DataFieldType.php'],
    ['Capabilities/Shapes/ClassShape/InspectDataShape.php', 'Capabilities/DataShapeInspection/InspectDataShape.php'],
    ['Capabilities/Shapes/ClassShape/CacheDataShape.php', 'Capabilities/DataShapeInspection/CacheDataShape.php'],
    ['Capabilities/Shapes/ClassShape/ReadClassDataShape.php', 'Capabilities/DataShapeInspection/ReadClassDataShape.php'],
    ['Capabilities/Shapes/ClassShape/ReadConstructorDataFields.php', 'Capabilities/DataShapeInspection/ReadConstructorDataFields.php'],
    ['Capabilities/Shapes/ClassShape/ReadPublicDataFields.php', 'Capabilities/DataShapeInspection/ReadPublicDataFields.php'],
    ['Capabilities/Shapes/ClassShape/ReadDataFieldAttributes.php', 'Capabilities/DataShapeInspection/ReadDataFieldAttributes.php'],
    ['Capabilities/Shapes/Visibility/ReadVisibleFields.php', 'Capabilities/DataShapeInspection/ReadVisibleFields.php'],
    ['Capabilities/Shapes/Visibility/ShouldExposeField.php', 'Capabilities/DataShapeInspection/ShouldExposeField.php'],
    ['Capabilities/Shapes/Visibility/HideFieldFromOutput.php', 'Capabilities/DataShapeInspection/HideFieldFromOutput.php'],

    // Group E: FieldMapping → Capabilities/FieldMapping
    ['Capabilities/Coercion/DtoSystem/FieldMapping/FieldInputName.php', 'Capabilities/FieldMapping/FieldInputName.php'],
    ['Capabilities/Coercion/DtoSystem/FieldMapping/MapInputNameToField.php', 'Capabilities/FieldMapping/MapInputNameToField.php'],
    ['Capabilities/Coercion/DtoSystem/FieldMapping/ReadMappedInputName.php', 'Capabilities/FieldMapping/ReadMappedInputName.php'],

    // Group F: TransferValidation → Capabilities/TransferValidation
    ['Capabilities/Coercion/DtoSystem/DataTransferFailure.php', 'Capabilities/TransferValidation/DataTransferFailure.php'],
    ['Capabilities/Coercion/DtoSystem/DataTransferException.php', 'Capabilities/TransferValidation/DataTransferException.php'],
    ['Capabilities/Coercion/DtoSystem/DataTransferResult.php', 'Capabilities/TransferValidation/DataTransferResult.php'],
    ['Capabilities/Coercion/DtoSystem/DataTransferViolation.php', 'Capabilities/TransferValidation/DataTransferViolation.php'],
    ['Capabilities/Coercion/DtoSystem/DataTransferViolations.php', 'Capabilities/TransferValidation/DataTransferViolations.php'],

    // Group G: PublicSurface → PublicSurface/
    ['Capabilities/Coercion/DtoSystem/DataTransfer.php', 'PublicSurface/DataTransfer.php'],
    ['Capabilities/Coercion/DtoSystem/DataObject.php', 'PublicSurface/DataObject.php'],

    // Group H: LegacyTransfer → Capabilities/LegacyTransfer
    ['Capabilities/Coercion/DtoSystem/AbstractDTO.php', 'Capabilities/LegacyTransfer/AbstractDTO.php'],
    ['Capabilities/Coercion/DtoSystem/SerializeLegacyDTO.php', 'Capabilities/LegacyTransfer/SerializeLegacyDTO.php'],
    ['Capabilities/Coercion/ObjectMapper/ObjectMapper.php', 'Capabilities/LegacyTransfer/ObjectMapper.php'],

    // Group I: ReadDataObject Flows → Flows/ReadDataObject
    ['Flows/ReadDataObject/ReadDataObject.php', 'Flows/ReadDataObject/ReadDataObject.php'],
    ['Flows/ReadDataObject/ReadDataObjectValues.php', 'Flows/ReadDataObject/ReadDataObjectValues.php'],
    ['Flows/ReadDataObject/ReadVisibleDataFields.php', 'Flows/ReadDataObject/ReadVisibleDataFields.php'],
    ['Flows/ReadDataObject/NormalizeDataObjectValue.php', 'Flows/ReadDataObject/NormalizeDataObjectValue.php'],

    // Group J: SerializeDataObject Flows → Flows/SerializeDataObject
    ['Flows/SerializeDataObject/SerializeDataObject.php', 'Flows/SerializeDataObject/SerializeDataObject.php'],
    ['Flows/SerializeDataObject/ConvertDataObjectToArray.php', 'Flows/SerializeDataObject/ConvertDataObjectToArray.php'],
    ['Flows/SerializeDataObject/ConvertDataObjectToJson.php', 'Flows/SerializeDataObject/ConvertDataObjectToJson.php'],
    ['Flows/SerializeDataObject/ConvertDataObjectToFlatArray.php', 'Flows/SerializeDataObject/ConvertDataObjectToFlatArray.php'],
    ['Flows/SerializeDataObject/ConvertDataObjectToStdClass.php', 'Flows/SerializeDataObject/ConvertDataObjectToStdClass.php'],
    ['Flows/SerializeDataObject/ConvertDataObjectToJsonApi.php', 'Flows/SerializeDataObject/ConvertDataObjectToJsonApi.php'],
];

// ── Namespace-level import replacements ──
// These are applied AFTER the generic namespace change.
// Order matters: more specific replacements first.

$importReplacements = [
    // Attributes: old DtoSystem/Attributes → new AttributeReading
    'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\Attributes\\'
    => 'Avax\\Components\\DataStack\\DataTransfer\\System\\Capabilities\\AttributeReading\\',

    // DataShapeInspection: old Shapes/ClassShape → new DataShapeInspection
    'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Shapes\\ClassShape\\'
    => 'Avax\\Components\\DataStack\\DataTransfer\\System\\Capabilities\\DataShapeInspection\\',

    // Visibility: old Shapes/Visibility → new DataShapeInspection
    'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Shapes\\Visibility\\'
    => 'Avax\\Components\\DataStack\\DataTransfer\\System\\Capabilities\\DataShapeInspection\\',

    // ValueConversion: old Coercion/DtoSystem/ValueConversion → new ValueConversion
    'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\ValueConversion\\'
    => 'Avax\\Components\\DataStack\\DataTransfer\\System\\Capabilities\\ValueConversion\\',

    // FieldMapping: old Coercion/DtoSystem/FieldMapping → new FieldMapping
    'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\FieldMapping\\'
    => 'Avax\\Components\\DataStack\\DataTransfer\\System\\Capabilities\\FieldMapping\\',

    // TransferValidation: old Coercion/DtoSystem root classes → new TransferValidation
    'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\DataTransferFailure'
    => 'Avax\\Components\\DataStack\\DataTransfer\\System\\Capabilities\\TransferValidation\\DataTransferFailure',
    'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\DataTransferException'
    => 'Avax\\Components\\DataStack\\DataTransfer\\System\\Capabilities\\TransferValidation\\DataTransferException',
    'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\DataTransferResult'
    => 'Avax\\Components\\DataStack\\DataTransfer\\System\\Capabilities\\TransferValidation\\DataTransferResult',
    'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\DataTransferViolation'
    => 'Avax\\Components\\DataStack\\DataTransfer\\System\\Capabilities\\TransferValidation\\DataTransferViolation',
    'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\DataTransferViolations'
    => 'Avax\\Components\\DataStack\\DataTransfer\\System\\Capabilities\\TransferValidation\\DataTransferViolations',

    // DataTransfer (PublicSurface): old Coercion/DtoSystem/DataTransfer → new PublicSurface
    'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\DataTransfer'
    => 'Avax\\Components\\DataStack\\DataTransfer\\System\\PublicSurface\\DataTransfer',

    // DataObject (PublicSurface): old Coercion/DtoSystem/DataObject → new PublicSurface
    'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\DataObject'
    => 'Avax\\Components\\DataStack\\DataTransfer\\System\\PublicSurface\\DataObject',

    // Configuration: old Coercion/DtoSystem/Configuration → new Configuration
    'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\Configuration\\'
    => 'Avax\\Components\\DataStack\\DataTransfer\\System\\Configuration\\',

    // ObjectMapper: old Coercion/ObjectMapper → new LegacyTransfer
    'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\ObjectMapper\\'
    => 'Avax\\Components\\DataStack\\DataTransfer\\System\\Capabilities\\LegacyTransfer\\',

    // AbstractDTO: old Coercion/DtoSystem/AbstractDTO → new LegacyTransfer
    'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\AbstractDTO'
    => 'Avax\\Components\\DataStack\\DataTransfer\\System\\Capabilities\\LegacyTransfer\\AbstractDTO',

    // SerializeLegacyDTO stays in LegacyTransfer
    'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\SerializeLegacyDTO'
    => 'Avax\\Components\\DataStack\\DataTransfer\\System\\Capabilities\\LegacyTransfer\\SerializeLegacyDTO',

    // Flows: old Flows/ReadDataObject → new Flows/ReadDataObject
    'Avax\\Components\\DataStack\\Data\\System\\Flows\\ReadDataObject\\'
    => 'Avax\\Components\\DataStack\\DataTransfer\\System\\Flows\\ReadDataObject\\',

    // Flows: old Flows/SerializeDataObject → new Flows/SerializeDataObject
    'Avax\\Components\\DataStack\\Data\\System\\Flows\\SerializeDataObject\\'
    => 'Avax\\Components\\DataStack\\DataTransfer\\System\\Flows\\SerializeDataObject\\',

    // Legacy DataShape references (for external files that may still reference old path)
    'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\'
    => 'Avax\\Components\\DataStack\\DataTransfer\\System\\Capabilities\\',
];

// ── Step 1: Create target directories ──

echo "Step 1: Creating target directories...\n";
$targetDirs = [
    $transferBase . '/PublicSurface',
    $transferBase . '/Capabilities/AttributeReading',
    $transferBase . '/Capabilities/DataShapeInspection',
    $transferBase . '/Capabilities/FieldMapping',
    $transferBase . '/Capabilities/ValueConversion',
    $transferBase . '/Capabilities/TransferValidation',
    $transferBase . '/Capabilities/LegacyTransfer',
    $transferBase . '/Configuration',
    $transferBase . '/Flows/ReadDataObject',
    $transferBase . '/Flows/SerializeDataObject',
];

foreach ($targetDirs as $dir) {
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "  Created: $dir\n";
    }
}

// ── Step 2: Copy files and rewrite content ──

echo "\nStep 2: Copying files and rewriting namespaces/imports...\n";
$moved = 0;

foreach ($files as [$oldRel, $newRel]) {
    $oldPath = $dataBase . '/' . $oldRel;
    $newPath = $transferBase . '/' . $newRel;

    if (! file_exists($oldPath)) {
        echo "  SKIP (not found): $oldRel\n";
        continue;
    }

    $content = file_get_contents($oldPath);

    // Determine new namespace from new relative path
    $newClassName = basename($newRel, '.php');
    $newDirRel    = dirname($newRel);
    if ($newDirRel === '.') {
        $newNamespace = $newNs;
    } else {
        $newNamespace = $newNs . '\\' . str_replace('/', '\\', $newDirRel);
    }

    // Rewrite namespace
    $content = preg_replace(
        '/^namespace\s+.+?;$/m',
        'namespace ' . $newNamespace . ';',
        $content,
        1
    );

    // Apply import replacements (order matters — most specific first)
    foreach ($importReplacements as $old => $new) {
        $content = str_replace($old, $new, $content);
    }

    // Ensure target directory exists
    $targetDir = dirname($newPath);
    if (! is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    file_put_contents($newPath, $content);
    echo "  Moved: $oldRel → $newRel\n";
    ++$moved;
}

echo "\nTotal files moved: $moved\n";

// ── Step 3: Delete old directories ──

echo "\nStep 3: Deleting old empty directories from DataStack/Data...\n";

$dirsToDelete = [
    $dataBase . '/Capabilities/Coercion/DtoSystem/Attributes',
    $dataBase . '/Capabilities/Coercion/DtoSystem/Configuration',
    $dataBase . '/Capabilities/Coercion/DtoSystem/FieldMapping',
    $dataBase . '/Capabilities/Coercion/DtoSystem/ValueConversion',
    $dataBase . '/Capabilities/Coercion/DtoSystem',
    $dataBase . '/Capabilities/Coercion/ObjectMapper',
    $dataBase . '/Capabilities/Coercion',
    $dataBase . '/Capabilities/Shapes/ClassShape',
    $dataBase . '/Capabilities/Shapes/Visibility',
    $dataBase . '/Capabilities/Shapes',
    $dataBase . '/Flows/ReadDataObject',
    $dataBase . '/Flows/SerializeDataObject',
];

foreach ($dirsToDelete as $dir) {
    if (is_dir($dir)) {
        // Only delete if empty or contains only moved files
        $files_in_dir = array_diff(scandir($dir), ['.', '..']);
        if (empty($files_in_dir)) {
            rmdir($dir);
            echo "  Removed: $dir\n";
        } else {
            echo "  WARN (not empty): $dir — contains: " . implode(', ', $files_in_dir) . "\n";
        }
    }
}

echo "\nMigration complete.\n";
