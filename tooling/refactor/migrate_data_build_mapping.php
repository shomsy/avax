<?php
/**
 * DataStack/Data Architecture Migration Script
 * Phase 1: Move files, update namespaces, update imports
 */

$sysBase = '/home/shomsy/projects/avax/components/DataStack/Data/System';

// Complete mapping: [oldPath relative to System, newPath relative to System, newNamespace]
$moves = [
    // VALUES
    ['Capabilities/DateTime/Moment.php', 'Capabilities/Values/Temporal/Moment.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Values\\Temporal'],
    ['Capabilities/Identity/Uuid.php', 'Capabilities/Values/Identity/Uuid.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Values\\Identity'],
    ['Capabilities/Money/Money.php', 'Capabilities/Values/Money/Money.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Values\\Money'],
    ['Capabilities/Money/Currency.php', 'Capabilities/Values/Money/Currency.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Values\\Money'],
    ['Capabilities/Numbers/Percentage.php', 'Capabilities/Values/Numeric/Percentage.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Values\\Numeric'],

    // STRUCTURES: Maps
    ['Capabilities/Map/Map.php', 'Capabilities/Structures/Maps/Map.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Maps'],
    ['Capabilities/OrderedMap/OrderedMap.php', 'Capabilities/Structures/Maps/OrderedMap.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Maps'],
    ['Capabilities/MultiMap/MultiMap.php', 'Capabilities/Structures/Maps/MultiMap.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Maps'],
    ['Capabilities/Structures/MapEntry.php', 'Capabilities/Structures/Maps/MapEntry.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Maps'],

    // STRUCTURES: Sets
    ['Capabilities/Set/Set.php', 'Capabilities/Structures/Sets/Set.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Sets'],
    ['Capabilities/OrderedSet/OrderedSet.php', 'Capabilities/Structures/Sets/OrderedSet.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Sets'],

    // STRUCTURES: Linear
    ['Capabilities/Sequence/Sequence.php', 'Capabilities/Structures/Linear/Sequence.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Linear'],
    ['Capabilities/DataList/DataList.php', 'Capabilities/Structures/Linear/DataList.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Linear'],

    // STRUCTURES: Functional
    ['Capabilities/Structures/Pair.php', 'Capabilities/Structures/Functional/Pair.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Functional'],
    ['Capabilities/Structures/Tuple2.php', 'Capabilities/Structures/Functional/Tuple2.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Functional'],
    ['Capabilities/Structures/Tuple3.php', 'Capabilities/Structures/Functional/Tuple3.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Functional'],
    ['Capabilities/Structures/Tuple4.php', 'Capabilities/Structures/Functional/Tuple4.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Functional'],
    ['Capabilities/Structures/Option/Option.php', 'Capabilities/Structures/Functional/Option/Option.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Functional\\Option'],
    ['Capabilities/Structures/Option/Some.php', 'Capabilities/Structures/Functional/Option/Some.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Functional\\Option'],
    ['Capabilities/Structures/Option/None.php', 'Capabilities/Structures/Functional/Option/None.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Functional\\Option'],
    ['Capabilities/Structures/Result/Result.php', 'Capabilities/Structures/Functional/Result/Result.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Functional\\Result'],
    ['Capabilities/Structures/Result/Success.php', 'Capabilities/Structures/Functional/Result/Success.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Functional\\Result'],
    ['Capabilities/Structures/Result/Failure.php', 'Capabilities/Structures/Functional/Result/Failure.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Functional\\Result'],
    ['Capabilities/Structures/Record.php', 'Capabilities/Structures/Functional/Record.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Functional'],
    ['Capabilities/Structures/RecordField.php', 'Capabilities/Structures/Functional/RecordField.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Functional'],
    ['Capabilities/Structures/OperationResult.php', 'Capabilities/Structures/Functional/OperationResult.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Functional'],
    ['Capabilities/Structures/Threshold.php', 'Capabilities/Structures/Functional/Threshold.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Functional'],
    ['Capabilities/Structures/DataStructure.php', 'Capabilities/Structures/Foundation/DataStructure.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Structures\\Foundation'],

    // LENSES
    ['Capabilities/DataPaths/DotPath.php', 'Capabilities/Lenses/DataPath/DotPath.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Lenses\\DataPath'],

    // OPERATORS: Transform
    ['Capabilities/Transform/MapValues.php', 'Capabilities/Operators/Transform/MapValues.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Transform'],
    ['Capabilities/Transform/FilterValues.php', 'Capabilities/Operators/Transform/FilterValues.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Transform'],
    ['Capabilities/Transform/RejectValues.php', 'Capabilities/Operators/Transform/RejectValues.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Transform'],
    ['Capabilities/Transform/ReduceValues.php', 'Capabilities/Operators/Transform/ReduceValues.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Transform'],
    ['Capabilities/Transform/FlattenValues.php', 'Capabilities/Operators/Transform/FlattenValues.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Transform'],
    ['Capabilities/Transform/GroupValues.php', 'Capabilities/Operators/Transform/GroupValues.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Transform'],
    ['Capabilities/Transform/PartitionValues.php', 'Capabilities/Operators/Transform/PartitionValues.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Transform'],
    ['Capabilities/Transform/ChunkValues.php', 'Capabilities/Operators/Transform/ChunkValues.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Transform'],
    ['Capabilities/Transform/FlipValues.php', 'Capabilities/Operators/Transform/FlipValues.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Transform'],
    ['Capabilities/Transform/EachValues.php', 'Capabilities/Operators/Transform/EachValues.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Transform'],
    ['Capabilities/Transform/AppendValue.php', 'Capabilities/Operators/Transform/AppendValue.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Transform'],
    ['Capabilities/Transform/PullValue.php', 'Capabilities/Operators/Transform/PullValue.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Transform'],
    ['Capabilities/Transform/PutValueByPath.php', 'Capabilities/Operators/Transform/PutValueByPath.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Transform'],
    ['Capabilities/Transform/ForgetValue.php', 'Capabilities/Operators/Transform/ForgetValue.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Transform'],
    ['Capabilities/Transform/JoinValues.php', 'Capabilities/Operators/Transform/JoinValues.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Transform'],
    ['Capabilities/Transform/LowercaseValues.php', 'Capabilities/Operators/Transform/LowercaseValues.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Transform'],
    ['Capabilities/Transform/UppercaseValues.php', 'Capabilities/Operators/Transform/UppercaseValues.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Transform'],
    ['Capabilities/Transform/TrimValues.php', 'Capabilities/Operators/Transform/TrimValues.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Transform'],
    ['Capabilities/Transform/UniqueValues.php', 'Capabilities/Operators/Transform/UniqueValues.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Transform'],

    // OPERATORS: Ordering
    ['Capabilities/Transform/SortValues.php', 'Capabilities/Operators/Ordering/SortValues.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Ordering'],
    ['Capabilities/Transform/SortValuesBy.php', 'Capabilities/Operators/Ordering/SortValuesBy.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Ordering'],
    ['Capabilities/Transform/ReverseValues.php', 'Capabilities/Operators/Ordering/ReverseValues.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Ordering'],
    ['Capabilities/Transform/ShuffleValues.php', 'Capabilities/Operators/Ordering/ShuffleValues.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Ordering'],
    ['Capabilities/Structures/Comparator.php', 'Capabilities/Operators/Ordering/Comparator.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Ordering'],

    // OPERATORS: Aggregate
    ['Capabilities/Aggregate/SumValues.php', 'Capabilities/Operators/Aggregate/SumValues.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Aggregate'],
    ['Capabilities/Aggregate/AverageValues.php', 'Capabilities/Operators/Aggregate/AverageValues.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Aggregate'],
    ['Capabilities/Aggregate/CountValues.php', 'Capabilities/Operators/Aggregate/CountValues.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Aggregate'],
    ['Capabilities/Aggregate/FindMaxValue.php', 'Capabilities/Operators/Aggregate/FindMaxValue.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Aggregate'],
    ['Capabilities/Aggregate/FindMinValue.php', 'Capabilities/Operators/Aggregate/FindMinValue.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Aggregate'],

    // OPERATORS: Search
    ['Capabilities/Search/ContainsValue.php', 'Capabilities/Operators/Search/ContainsValue.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Search'],
    ['Capabilities/Search/SearchValue.php', 'Capabilities/Operators/Search/SearchValue.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Search'],
    ['Capabilities/Search/MatchTextFuzzily.php', 'Capabilities/Operators/Search/MatchTextFuzzily.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Search'],
    ['Capabilities/Search/MatchTextPartially.php', 'Capabilities/Operators/Search/MatchTextPartially.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Search'],

    // OPERATORS: Selection
    ['Capabilities/Selection/HasValue.php', 'Capabilities/Operators/Selection/HasValue.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Selection'],
    ['Capabilities/Selection/ReadValueByPath.php', 'Capabilities/Operators/Selection/ReadValueByPath.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Selection'],

    // OPERATORS: Arrays
    ['Capabilities/Arrays/ArrayReader.php', 'Capabilities/Operators/Arrays/ArrayReader.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Arrays'],
    ['Capabilities/Arrays/ArrayWriter.php', 'Capabilities/Operators/Arrays/ArrayWriter.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\Arrays'],

    // OPERATORS: ObjectReading
    ['Capabilities/ObjectReading/ReadDataObject.php', 'Capabilities/Operators/ObjectReading/ReadDataObject.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\ObjectReading'],
    ['Capabilities/ObjectReading/NormalizeDataObjectValue.php', 'Capabilities/Operators/ObjectReading/NormalizeDataObjectValue.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Operators\\ObjectReading'],

    // CODECS
    ['Capabilities/Transform/ConvertCollectionToJson.php', 'Capabilities/Codecs/JsonCodec/EncodeJson.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Codecs\\JsonCodec'],
    ['Capabilities/Transform/ConvertCollectionToXml.php', 'Capabilities/Codecs/XmlCodec/EncodeXml.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Codecs\\XmlCodec'],
    ['Capabilities/Transform/ConvertCollectionToArray.php', 'Capabilities/Codecs/ArrayCodec/EncodeArray.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Codecs\\ArrayCodec'],

    // SHAPES: ClassShape
    ['Capabilities/DataShape/DataShape.php', 'Capabilities/Shapes/ClassShape/DataShape.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Shapes\\ClassShape'],
    ['Capabilities/DataShape/DataField.php', 'Capabilities/Shapes/ClassShape/DataField.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Shapes\\ClassShape'],
    ['Capabilities/DataShape/DataFieldType.php', 'Capabilities/Shapes/ClassShape/DataFieldType.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Shapes\\ClassShape'],
    ['Capabilities/DataShape/InspectDataShape.php', 'Capabilities/Shapes/ClassShape/InspectDataShape.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Shapes\\ClassShape'],
    ['Capabilities/DataShape/ReadClassDataShape.php', 'Capabilities/Shapes/ClassShape/ReadClassDataShape.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Shapes\\ClassShape'],
    ['Capabilities/DataShape/ReadConstructorDataFields.php', 'Capabilities/Shapes/ClassShape/ReadConstructorDataFields.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Shapes\\ClassShape'],
    ['Capabilities/DataShape/ReadDataFieldAttributes.php', 'Capabilities/Shapes/ClassShape/ReadDataFieldAttributes.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Shapes\\ClassShape'],
    ['Capabilities/DataShape/ReadPublicDataFields.php', 'Capabilities/Shapes/ClassShape/ReadPublicDataFields.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Shapes\\ClassShape'],
    ['Capabilities/DataShape/CacheDataShape.php', 'Capabilities/Shapes/ClassShape/CacheDataShape.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Shapes\\ClassShape'],

    // SHAPES: Visibility
    ['Capabilities/FieldVisibility/HideFieldFromOutput.php', 'Capabilities/Shapes/Visibility/HideFieldFromOutput.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Shapes\\Visibility'],
    ['Capabilities/FieldVisibility/ShouldExposeField.php', 'Capabilities/Shapes/Visibility/ShouldExposeField.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Shapes\\Visibility'],
    ['Capabilities/FieldVisibility/ReadVisibleFields.php', 'Capabilities/Shapes/Visibility/ReadVisibleFields.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Shapes\\Visibility'],

    // COERCION: DtoSystem
    ['Capabilities/DataTransfer/DataTransfer.php', 'Capabilities/Coercion/DtoSystem/DataTransfer.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem'],
    ['Capabilities/DataTransfer/DataTransferResult.php', 'Capabilities/Coercion/DtoSystem/DataTransferResult.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem'],
    ['Capabilities/DataTransfer/DataTransferFailure.php', 'Capabilities/Coercion/DtoSystem/DataTransferFailure.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem'],
    ['Capabilities/DataTransfer/DataTransferViolation.php', 'Capabilities/Coercion/DtoSystem/DataTransferViolation.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem'],
    ['Capabilities/DataTransfer/DataTransferViolations.php', 'Capabilities/Coercion/DtoSystem/DataTransferViolations.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem'],
    ['Capabilities/DataTransfer/DataTransferException.php', 'Capabilities/Coercion/DtoSystem/DataTransferException.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem'],
    ['Capabilities/DataTransfer/DataObject.php', 'Capabilities/Coercion/DtoSystem/DataObject.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem'],
    ['Capabilities/DataTransfer/Foundation/AbstractDTO.php', 'Capabilities/Coercion/DtoSystem/AbstractDTO.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem'],
    ['Capabilities/DataTransfer/Compatibility/SerializeLegacyDTO.php', 'Capabilities/Coercion/DtoSystem/SerializeLegacyDTO.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem'],
    ['Capabilities/DataTransfer/Capabilities/Attributes/CastWith.php', 'Capabilities/Coercion/DtoSystem/Attributes/CastWith.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\Attributes'],
    ['Capabilities/DataTransfer/Capabilities/Attributes/DefaultValue.php', 'Capabilities/Coercion/DtoSystem/Attributes/DefaultValue.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\Attributes'],
    ['Capabilities/DataTransfer/Capabilities/Attributes/Hidden.php', 'Capabilities/Coercion/DtoSystem/Attributes/Hidden.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\Attributes'],
    ['Capabilities/DataTransfer/Capabilities/Attributes/ListOf.php', 'Capabilities/Coercion/DtoSystem/Attributes/ListOf.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\Attributes'],
    ['Capabilities/DataTransfer/Capabilities/Attributes/MapFrom.php', 'Capabilities/Coercion/DtoSystem/Attributes/MapFrom.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\Attributes'],
    ['Capabilities/DataTransfer/Capabilities/Attributes/Optional.php', 'Capabilities/Coercion/DtoSystem/Attributes/Optional.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\Attributes'],
    ['Capabilities/DataTransfer/Capabilities/Attributes/Required.php', 'Capabilities/Coercion/DtoSystem/Attributes/Required.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\Attributes'],
    ['Capabilities/DataTransfer/Capabilities/ErrorReporting/DataTransferFailure.php', 'Capabilities/Coercion/DtoSystem/DataTransferError.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem'],
    ['Capabilities/DataTransfer/Capabilities/FieldMapping/FieldInputName.php', 'Capabilities/Coercion/DtoSystem/FieldMapping/FieldInputName.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\FieldMapping'],
    ['Capabilities/DataTransfer/Capabilities/FieldMapping/MapInputNameToField.php', 'Capabilities/Coercion/DtoSystem/FieldMapping/MapInputNameToField.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\FieldMapping'],
    ['Capabilities/DataTransfer/Capabilities/FieldMapping/ReadMappedInputName.php', 'Capabilities/Coercion/DtoSystem/FieldMapping/ReadMappedInputName.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\FieldMapping'],
    ['Capabilities/DataTransfer/Capabilities/ValueConversion/ValueCasterInterface.php', 'Capabilities/Coercion/DtoSystem/ValueConversion/ValueCasterInterface.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\ValueConversion'],
    ['Capabilities/DataTransfer/Capabilities/ValueConversion/ValueConversionContext.php', 'Capabilities/Coercion/DtoSystem/ValueConversion/ValueConversionContext.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\ValueConversion'],
    ['Capabilities/DataTransfer/Configuration/DataTransferConfig.php', 'Capabilities/Coercion/DtoSystem/Configuration/DataTransferConfig.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\Configuration'],
    ['Capabilities/DataTransfer/Configuration/UnknownFieldPolicy.php', 'Capabilities/Coercion/DtoSystem/Configuration/UnknownFieldPolicy.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\DtoSystem\\Configuration'],

    // COERCION: ObjectMapper
    ['Capabilities/ObjectHandling/ObjectMapper.php', 'Capabilities/Coercion/ObjectMapper/ObjectMapper.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\ObjectMapper'],
    ['Capabilities/ObjectHandling/DTO/DTOValidationException.php', 'Capabilities/Coercion/ObjectMapper/DTOValidationException.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Coercion\\ObjectMapper'],

    // VALIDATION
    ['Capabilities/Validation/Validator.php', 'Capabilities/Validation/Rules/Validator.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Validation\\Rules'],
    ['Capabilities/Validation/DatabaseValidator.php', 'Capabilities/Validation/Rules/DatabaseValidator.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Validation\\Rules'],
    ['Capabilities/Validation/Attributes/Rules/EmailRule.php', 'Capabilities/Validation/Rules/EmailRule.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Validation\\Rules'],
    ['Capabilities/Validation/Attributes/Rules/MinLengthRule.php', 'Capabilities/Validation/Rules/MinLengthRule.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Validation\\Rules'],
    ['Capabilities/Validation/Rules/PasswordComplexityRule.php', 'Capabilities/Validation/Rules/PasswordComplexityRule.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Validation\\Rules'],

    // FORMS
    ['Capabilities/Arrhae/Arrhae.php', 'Capabilities/Forms/ArrayForm/Arrhae.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Forms\\ArrayForm'],
    ['Capabilities/Collection/Collection.php', 'Capabilities/Forms/CollectionForm/Collection.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Forms\\CollectionForm'],
    ['Capabilities/Collection/CollectionInterface.php', 'Capabilities/Forms/CollectionForm/CollectionInterface.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Forms\\CollectionForm'],
    ['Capabilities/Json/Json.php', 'Capabilities/Forms/JsonForm/Json.php', 'Avax\\Components\\DataStack\\Data\\System\\Capabilities\\Forms\\JsonForm'],
];

file_put_contents('/home/shomsy/projects/avax/tooling/refactor/migrate_data_mapping.json', json_encode($moves, JSON_PRETTY_PRINT));
echo "Mapping saved: " . count($moves) . " moves\n";
