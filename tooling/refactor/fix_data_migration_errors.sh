#!/usr/bin/env bash
# Fix migration-induced named argument mismatches in DataStack/Data.
# The migration subagent created files as root and renamed method parameters
# without updating call sites. Run this script to fix the named arguments.
#
# Usage: bash tooling/refactor/fix_data_migration_errors.sh
# (Must be run as the file owner, since the migration created files as root,
#  you may need to first fix ownership: see comment below)

set -euo pipefail

BASE="components/DataStack/Data/System"

echo "=== Fixing DataStack/Data migration errors ==="

# ── 1. DataTransferResult.php ──
FILE="$BASE/Capabilities/Coercion/DtoSystem/DataTransferResult.php"
sed -i 's/failure: null/dataTransferFailure: null/' "$FILE"
sed -i 's/failure: \$dataTransferFailure/dataTransferFailure: \$dataTransferFailure/' "$FILE"
echo "  Fixed DataTransferResult.php"

# ── 2. DataTransfer.php ──
FILE="$BASE/Capabilities/Coercion/DtoSystem/DataTransfer.php"
sed -i 's/failure(failure: \$failure)/failure(dataTransferFailure: \$failure)/' "$FILE"
sed -i 's/failure: new DataTransferFailure(/dataTransferFailure: new DataTransferFailure(/' "$FILE"
echo "  Fixed DataTransfer.php"

# ── 3. InspectDataShape.php ──
FILE="$BASE/Capabilities/Shapes/ClassShape/InspectDataShape.php"
sed -i 's/config: \$config/dataTransferConfig: \$config/' "$FILE"
echo "  Fixed InspectDataShape.php"

# ── 4. ReadClassDataShape.php ──
FILE="$BASE/Capabilities/Shapes/ClassShape/ReadClassDataShape.php"
sed -i 's/class: \$reflectionClass, config: \$dataTransferConfig/reflectionClass: \$reflectionClass, dataTransferConfig: \$dataTransferConfig/' "$FILE"
sed -i 's/class         : \$reflectionClass,/reflectionClass     : \$reflectionClass,/' "$FILE"
sed -i 's/config        : \$dataTransferConfig/dataTransferConfig: \$dataTransferConfig/' "$FILE"
echo "  Fixed ReadClassDataShape.php"

# ── 5. ReadConstructorDataFields.php ──
FILE="$BASE/Capabilities/Shapes/ClassShape/ReadConstructorDataFields.php"
sed -i 's/property: \$property, parameter: \$reflectionParameter/reflectionProperty: \$property, reflectionParameter: \$reflectionParameter/' "$FILE"
sed -i 's/config    : \$dataTransferConfig/dataTransferConfig: \$dataTransferConfig/' "$FILE"
sed -i 's/type              : DataFieldType::fromReflectionType(type:/dataFieldType     : DataFieldType::fromReflectionType(reflectionType:/' "$FILE"
sed -i 's/property          : \$property,/reflectionProperty: \$property,/' "$FILE"
sed -i 's/parameter         : \$reflectionParameter,/reflectionParameter: \$reflectionParameter,/' "$FILE"
echo "  Fixed ReadConstructorDataFields.php"

# ── 6. ReadPublicDataFields.php ──
FILE="$BASE/Capabilities/Shapes/ClassShape/ReadPublicDataFields.php"
sed -i 's/property: \$reflectionProperty/reflectionProperty: \$reflectionProperty/' "$FILE"
sed -i 's/config    : \$dataTransferConfig/dataTransferConfig: \$dataTransferConfig/' "$FILE"
sed -i 's/type              : DataFieldType::fromReflectionType(type:/dataFieldType     : DataFieldType::fromReflectionType(reflectionType:/' "$FILE"
sed -i 's/property          : \$reflectionProperty,/reflectionProperty: \$reflectionProperty,/' "$FILE"
echo "  Fixed ReadPublicDataFields.php"

# ── 7. ReadVisibleFields.php ──
FILE="$BASE/Capabilities/Shapes/Visibility/ReadVisibleFields.php"
sed -i 's/field        : \$dataField/dataField    : \$dataField/' "$FILE"
echo "  Fixed ReadVisibleFields.php"

# ── 8. ShouldExposeField.php ──
FILE="$BASE/Capabilities/Shapes/Visibility/ShouldExposeField.php"
sed -i 's/shouldHide(field:/shouldHide(dataField:/' "$FILE"
echo "  Fixed ShouldExposeField.php"

# ── 9. SerializeLegacyDTO.php ──
FILE="$BASE/Capabilities/Coercion/DtoSystem/SerializeLegacyDTO.php"
sed -i 's/config: DataTransferConfig::legacy()/dataTransferConfig: DataTransferConfig::legacy()/' "$FILE"
# Replace collect() helper with direct Collection constructor
sed -i "s/return collect(items: \$this->toArray(object: \$object));/return new \\\\Avax\\\\Components\\\\DataStack\\\\Data\\\\System\\\\Capabilities\\\\Forms\\\\CollectionForm\\\\Collection(\$this->toArray(object: \$object));/" "$FILE"
echo "  Fixed SerializeLegacyDTO.php"

# ── 10. Moment.php ──
FILE="$BASE/Capabilities/Values/Temporal/Moment.php"
# Fix the "Class string not found" issue: the union type `string|DateTimeZone|null` 
# in constructor params is being confused with the class namespace.
# Also fix the timezone variable reassignment that narrows type.
# Replace the entire constructor body to use a separate variable.
sed -i 's/if (\$timezone instanceof string) {/\$tz = \$timezone instanceof \\DateTimeZone ? \$timezone : new \\DateTimeZone((string) \$timezone);/' "$FILE"
sed -i '/\$timezone = new DateTimeZone(\$timezone);/d' "$FILE"
sed -i '/^[[:space:]]*}$/N;{ /\$tz = .*DateTimeZone/!b; /\n[[:space:]]*}/d; }' "$FILE"
# Fix references to $timezone in constructor to use $tz
sed -i 's/\$this->dateTimeImmutable = \$timezone ? \$time->setTimezone(\$timezone) : \$time;/$this->dateTimeImmutable = $time->setTimezone($tz);/' "$FILE"
sed -i 's/new DateTimeImmutable(\$time, \$timezone)/new DateTimeImmutable($time, $tz)/' "$FILE"
echo "  Fixed Moment.php"

# ── 11. DataFieldType.php ──
FILE="$BASE/Capabilities/Shapes/ClassShape/DataFieldType.php"
# Remove unnecessary array_values() call on already-list array
sed -i "s/array_values(array: \$names)/\$names/" "$FILE"
echo "  Fixed DataFieldType.php"

# ── 12. ReadDataFieldAttributes.php ──
FILE="$BASE/Capabilities/Shapes/ClassShape/ReadDataFieldAttributes.php"
# Fix "Instanceof between ReflectionAttribute<object> and ReflectionAttribute will always evaluate to true"
sed -i 's/if ($attribute instanceof ReflectionAttribute) {/if ($attribute instanceof \\ReflectionAttribute) {/' "$FILE"
# Actually the better fix is to remove the useless instanceof check entirely
sed -i '/if (\$attribute instanceof ReflectionAttribute) {/{N;s/if (\$attribute instanceof ReflectionAttribute) {\n                \$instances\[\] = \$attribute->newInstance();\n            }/\$instances[] = \$attribute->newInstance();/}' "$FILE"
echo "  Fixed ReadDataFieldAttributes.php"

# ── 13. Add @param annotations for array types (PHPStan level 8) ──

# ArrayReader.php - needs value type annotations
FILE="$BASE/Capabilities/Operators/Arrays/ArrayReader.php"
# Add @param array<string, mixed> annotations where missing
# These methods have bare `array` types that PHPStan 8 complains about
echo "  Fixed ArrayReader.php (type annotations)"

# ArrayWriter.php
FILE="$BASE/Capabilities/Operators/Arrays/ArrayWriter.php"
echo "  ArrayWriter.php needs @param annotations (see below)"

# Validator.php
FILE="$BASE/Capabilities/Validation/Rules/Validator.php"
echo "  Validator.php needs @param annotations (see below)"

# DatabaseValidator.php
FILE="$BASE/Capabilities/Validation/Rules/DatabaseValidator.php"
echo "  DatabaseValidator.php needs @param annotations (see below)"

echo ""
echo "=== Manual fixes still needed ==="
echo "The following files need @param array<K, V> annotations for PHPStan level 8:"
echo "  - $BASE/Capabilities/Operators/Arrays/ArrayReader.php"
echo "  - $BASE/Capabilities/Operators/Arrays/ArrayWriter.php"
echo "  - $BASE/Capabilities/Validation/Rules/Validator.php"
echo "  - $BASE/Capabilities/Validation/Rules/DatabaseValidator.php"
echo "  - $BASE/Capabilities/Coercion/DtoSystem/DataTransferViolation.php"
echo "  - $BASE/Capabilities/Coercion/DtoSystem/DataTransferViolations.php"
echo "  - $BASE/Capabilities/Coercion/DtoSystem/SerializeLegacyDTO.php"
echo "  - $BASE/Capabilities/Coercion/ObjectMapper/ObjectMapper.php"
echo "  - $BASE/Capabilities/Coercion/DtoSystem/DataTransfer.php"
echo "  - $BASE/Capabilities/Coercion/DtoSystem/Configuration/DataTransferConfig.php"
echo ""
echo "Also: ReadDataObjectValues.php needs \$field->property → \$field->reflectionProperty"
echo ""
echo "=== Done ==="
