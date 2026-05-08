# Data Engine Architecture Reconciliation — Audit Report

**Date:** 2026-05-08
**Stage:** V3 Data Engine — Phase 1 Audit
**Status:** GREEN — Audit complete, plan approved, moves deferred to next task

## 1. Current DataStack/Data Tree

166 PHP files in `components/DataStack/Data/System/`:

```
System/
  Capabilities/
    Aggregate/          5 files  (SumValues, AverageValues, CountValues, FindMaxValue, FindMinValue)
    Arrays/             2 files  (ArrayReader, ArrayWriter)
    Arrhae/             1 file   (Arrhae.php — array-native DSL)
    Collection/         2 files  (Collection.php, CollectionInterface.php)
    DataList/           1 file   (DataList.php)
    DataPaths/          1 file   (DotPath.php)
    DataShape/          9 files  (DataShape, DataField, DataFieldType, InspectDataShape, ReadClassDataShape, ReadConstructorDataFields, ReadDataFieldAttributes, ReadPublicDataFields, CacheDataShape)
    DataTransfer/       22 files (DataTransfer, DataTransferResult, DataTransferFailure×2, DataTransferViolation, DataTransferViolations, DataTransferException, DataObject, AbstractDTO, SerializeLegacyDTO, 7 attributes, 3 field mapping, 2 value conversion, 2 config, 1 error reporting)
    DateTime/           1 file   (Moment.php)
    FieldVisibility/    3 files  (HideFieldFromOutput, ShouldExposeField, ReadVisibleFields)
    Foundation/         6 files  (Mutability/MutationGuard, Normalization/MakeCollection+NormalizedIterable+WrapValue)
    Identity/           1 file   (Uuid.php)
    Json/               1 file   (Json.php — JSON DSL)
    Map/                1 file   (Map.php)
    Money/              2 files  (Money.php, Currency.php)
    MultiMap/           1 file   (MultiMap.php)
    Numbers/            1 file   (Percentage.php)
    ObjectHandling/     2 files  (ObjectMapper.php, DTO/DTOValidationException.php)
    ObjectReading/      2 files  (NormalizeDataObjectValue, ReadDataObject)
    OrderedMap/         1 file   (OrderedMap.php)
    OrderedSet/         1 file   (OrderedSet.php)
    Search/             4 files  (ContainsValue, SearchValue, MatchTextFuzzily, MatchTextPartially)
    Selection/          2 files  (HasValue, ReadValueByPath)
    Sequence/           1 file   (Sequence.php)
    Set/                1 file   (Set.php)
    Structures/         18 files (Comparator, DataStructure, MapEntry, OperationResult, Pair, Record, RecordField, Threshold, Tuple2, Tuple3, Tuple4, Option/Option+Some+None, Result/Result+Success+Failure)
    Transform/          27 files (MapValues, FilterValues, RejectValues, ReduceValues, FlattenValues, AppendValue, PullValue, PutValueByPath, ForgetValue, ConvertCollectionToJson, ConvertCollectionToXml, ConvertCollectionToArray, ChunkValues, FlipValues, GroupValues, JoinValues, LowercaseValues, PartitionValues, ReverseValues, ShuffleValues, SortValues, SortValuesBy, TrimValues, UniqueValues, UppercaseValues, EachValues)
    Validation/         5 files  (Validator, DatabaseValidator, Attributes/Rules/EmailRule, Attributes/Rules/MinLengthRule, Rules/PasswordComplexityRule)
  Configuration/
    RegisterDataDependencies.php
  Flows/
    Aggregate/          4 files  (AverageValues, MaxValue, MinValue, SumValues)
    Batch/              1 file   (Batch.php)
    CreateCollection/   1 file   (CreateCollection.php)
    LazySequence/       1 file   (LazySequence.php)
    Normalize/          1 file   (NormalizeData.php)
    Pipeline/           2 files  (Pipe.php, Pipeline.php)
    Read/               1 file   (ReadNestedValue.php)
    ReadDataObject/     4 files  (NormalizeDataObjectValue, ReadDataObject, ReadDataObjectValues, ReadVisibleDataFields)
    ReadDataValue/      1 file   (ReadDataValue.php)
    SerializeDataObject/6 files  (ConvertDataObjectToArray/FlatArray/Json/JsonApi/StdClass, SerializeDataObject)
    Transform/          4 files  (FlattenItems, GroupItemsBy, KeyItemsBy, SortItems)
    TransformData/      1 file   (TransformData.php)
    Window/             1 file   (Window.php)
    Write/              1 file   (WriteNestedValue.php)
    WriteDataValue/     1 file   (WriteDataValue.php)
  Foundation/
    EXCEPTIONS/         3 files  (DataException, InvalidFlowException, InvalidValueException)
    Failure/            3 files  (DataFailure, InvalidJson, MutationException)
    Mutability/         1 file   (MutationGuard)
    Normalization/      3 files  (MakeCollection, NormalizedIterable, WrapValue)
  PublicSurface/
    Data.php
    DataInterface.php
    shortcuts.php
```

## 2. Complete Classification Table

### Forms (3 DSL facades)

| Class | Current Path | Category | Target | Reason |
|-------|-------------|----------|--------|--------|
| Arrhae | Capabilities/Arrhae/Arrhae.php | Form (ArrayForm) | Forms/ArrayForm/Arrhae.php | Array representation DSL, the core engine |
| Collection | Capabilities/Collection/Collection.php | Form (CollectionForm) | Forms/CollectionForm/Collection.php | Collection representation DSL, composes Arrhae |
| CollectionInterface | Capabilities/Collection/CollectionInterface.php | Form (CollectionForm) | Forms/CollectionForm/CollectionInterface.php | Collection contract |
| Json | Capabilities/Json/Json.php | Form (JsonForm) | Forms/JsonForm/Json.php | JSON representation DSL, composes Arrhae |

### Shapes (class reflection + visibility)

| Class | Current Path | Category | Target | Reason |
|-------|-------------|----------|--------|--------|
| DataShape | Capabilities/DataShape/DataShape.php | Shape (ClassShape) | Shapes/ClassShape/DataShape.php | Class shape value object |
| DataField | Capabilities/DataShape/DataField.php | Shape (ClassShape) | Shapes/ClassShape/DataField.php | Field descriptor |
| DataFieldType | Capabilities/DataShape/DataFieldType.php | Shape (ClassShape) | Shapes/ClassShape/DataFieldType.php | Field type descriptor |
| InspectDataShape | Capabilities/DataShape/InspectDataShape.php | Shape (ClassShape) | Shapes/ClassShape/InspectDataShape.php | Shape inspector capability |
| ReadClassDataShape | Capabilities/DataShape/ReadClassDataShape.php | Shape (ClassShape) | Shapes/ClassShape/ReadClassDataShape.php | Class shape reader |
| ReadConstructorDataFields | Capabilities/DataShape/ReadConstructorDataFields.php | Shape (ClassShape) | Shapes/ClassShape/ReadConstructorDataFields.php | Constructor field reader |
| ReadDataFieldAttributes | Capabilities/DataShape/ReadDataFieldAttributes.php | Shape (ClassShape) | Shapes/ClassShape/ReadDataFieldAttributes.php | Attribute reader |
| ReadPublicDataFields | Capabilities/DataShape/ReadPublicDataFields.php | Shape (ClassShape) | Shapes/ClassShape/ReadPublicDataFields.php | Public field reader |
| CacheDataShape | Capabilities/DataShape/CacheDataShape.php | Shape (ClassShape) | Shapes/ClassShape/CacheDataShape.php | Shape cache |
| HideFieldFromOutput | Capabilities/FieldVisibility/HideFieldFromOutput.php | Shape (Visibility) | Shapes/Visibility/HideFieldFromOutput.php | Field visibility control |
| ShouldExposeField | Capabilities/FieldVisibility/ShouldExposeField.php | Shape (Visibility) | Shapes/Visibility/ShouldExposeField.php | Field visibility control |
| ReadVisibleFields | Capabilities/FieldVisibility/ReadVisibleFields.php | Shape (Visibility) | Shapes/Visibility/ReadVisibleFields.php | Visible field reader |

### Structures (data structure invariants)

| Class | Current Path | Category | Target | Reason |
|-------|-------------|----------|--------|--------|
| Map | Capabilities/Map/Map.php | Structure (Maps) | Structures/Maps/Map.php | Key→value invariant |
| OrderedMap | Capabilities/OrderedMap/OrderedMap.php | Structure (Maps) | Structures/Maps/OrderedMap.php | Ordered key→value |
| MultiMap | Capabilities/MultiMap/MultiMap.php | Structure (Maps) | Structures/Maps/MultiMap.php | Key→many values |
| MapEntry | Capabilities/Structures/MapEntry.php | Structure (Maps) | Structures/Maps/MapEntry.php | Map entry value object |
| Set | Capabilities/Set/Set.php | Structure (Sets) | Structures/Sets/Set.php | Unique values invariant |
| OrderedSet | Capabilities/OrderedSet/OrderedSet.php | Structure (Sets) | Structures/Sets/OrderedSet.php | Ordered unique values |
| Sequence | Capabilities/Sequence/Sequence.php | Structure (Linear) | Structures/Linear/Sequence.php | Integer-indexed ordered |
| DataList | Capabilities/DataList/DataList.php | Structure (Linear) | Structures/Linear/DataList.php | Extended ordered list (rename deferred) |
| Pair | Capabilities/Structures/Pair.php | Structure (Functional) | Structures/Functional/Pair.php | Two-value tuple |
| Tuple2 | Capabilities/Structures/Tuple2.php | Structure (Functional) | Structures/Functional/Tuple2.php | 2-value tuple |
| Tuple3 | Capabilities/Structures/Tuple3.php | Structure (Functional) | Structures/Functional/Tuple3.php | 3-value tuple |
| Tuple4 | Capabilities/Structures/Tuple4.php | Structure (Functional) | Structures/Functional/Tuple4.php | 4-value tuple |
| Option | Capabilities/Structures/Option/Option.php | Structure (Functional) | Structures/Functional/Option/Option.php | Maybe monad |
| Some | Capabilities/Structures/Option/Some.php | Structure (Functional) | Structures/Functional/Option/Some.php | Some variant |
| None | Capabilities/Structures/Option/None.php | Structure (Functional) | Structures/Functional/Option/None.php | None variant |
| Result | Capabilities/Structures/Result/Result.php | Structure (Functional) | Structures/Functional/Result/Result.php | Either monad |
| Success | Capabilities/Structures/Result/Success.php | Structure (Functional) | Structures/Functional/Result/Success.php | Success variant |
| Failure | Capabilities/Structures/Result/Failure.php | Structure (Functional) | Structures/Functional/Result/Failure.php | Failure variant |
| Record | Capabilities/Structures/Record.php | Structure (Functional) | Structures/Functional/Record.php | Named field value |
| RecordField | Capabilities/Structures/RecordField.php | Structure (Functional) | Structures/Functional/RecordField.php | Record field descriptor |

### Operators (value operations)

| Class | Current Path | Category | Target | Reason |
|-------|-------------|----------|--------|--------|
| MapValues | Capabilities/Transform/MapValues.php | Operator (Transform) | Operators/Transform/MapValues.php | Transform each value |
| FilterValues | Capabilities/Transform/FilterValues.php | Operator (Transform) | Operators/Transform/FilterValues.php | Keep matching values |
| RejectValues | Capabilities/Transform/RejectValues.php | Operator (Transform) | Operators/Transform/RejectValues.php | Remove matching values |
| ReduceValues | Capabilities/Transform/ReduceValues.php | Operator (Transform) | Operators/Transform/ReduceValues.php | Fold to single value |
| FlattenValues | Capabilities/Transform/FlattenValues.php | Operator (Transform) | Operators/Transform/FlattenValues.php | Flatten nested arrays |
| GroupValues | Capabilities/Transform/GroupValues.php | Operator (Transform) | Operators/Transform/GroupValues.php | Group by key |
| PartitionValues | Capabilities/Transform/PartitionValues.php | Operator (Transform) | Operators/Transform/PartitionValues.php | Split by predicate |
| ChunkValues | Capabilities/Transform/ChunkValues.php | Operator (Transform) | Operators/Transform/ChunkValues.php | Split into chunks |
| FlipValues | Capabilities/Transform/FlipValues.php | Operator (Transform) | Operators/Transform/FlipValues.php | Swap keys and values |
| EachValues | Capabilities/Transform/EachValues.php | Operator (Transform) | Operators/Transform/EachValues.php | Side-effect iteration |
| AppendValue | Capabilities/Transform/AppendValue.php | Operator (Transform) | Operators/Transform/AppendValue.php | Append to array |
| PullValue | Capabilities/Transform/PullValue.php | Operator (Transform) | Operators/Transform/PullValue.php | Extract and remove |
| PutValueByPath | Capabilities/Transform/PutValueByPath.php | Operator (Transform) | Operators/Transform/PutValueByPath.php | Set by path |
| ForgetValue | Capabilities/Transform/ForgetValue.php | Operator (Transform) | Operators/Transform/ForgetValue.php | Remove by path |
| ConvertCollectionToJson | Capabilities/Transform/ConvertCollectionToJson.php | Operator (Transform) | Operators/Transform/ConvertCollectionToJson.php | Array→JSON string |
| ConvertCollectionToXml | Capabilities/Transform/ConvertCollectionToXml.php | Operator (Transform) | Operators/Transform/ConvertCollectionToXml.php | Array→XML string |
| ConvertCollectionToArray | Capabilities/Transform/ConvertCollectionToArray.php | Operator (Transform) | Operators/Transform/ConvertCollectionToArray.php | Normalize to array |
| JoinValues | Capabilities/Transform/JoinValues.php | Operator (Transform) | Operators/Transform/JoinValues.php | Join string values |
| LowercaseValues | Capabilities/Transform/LowercaseValues.php | Operator (Transform) | Operators/Transform/LowercaseValues.php | Lowercase strings |
| UppercaseValues | Capabilities/Transform/UppercaseValues.php | Operator (Transform) | Operators/Transform/UppercaseValues.php | Uppercase strings |
| TrimValues | Capabilities/Transform/TrimValues.php | Operator (Transform) | Operators/Transform/TrimValues.php | Trim whitespace |
| UniqueValues | Capabilities/Transform/UniqueValues.php | Operator (Transform) | Operators/Transform/UniqueValues.php | Deduplicate values |
| SortValues | Capabilities/Transform/SortValues.php | Operator (Ordering) | Operators/Ordering/SortValues.php | Sort values |
| SortValuesBy | Capabilities/Transform/SortValuesBy.php | Operator (Ordering) | Operators/Ordering/SortValuesBy.php | Sort by key |
| ReverseValues | Capabilities/Transform/ReverseValues.php | Operator (Ordering) | Operators/Ordering/ReverseValues.php | Reverse order |
| ShuffleValues | Capabilities/Transform/ShuffleValues.php | Operator (Ordering) | Operators/Ordering/ShuffleValues.php | Random shuffle |
| Comparator | Capabilities/Structures/Comparator.php | Operator (Ordering) | Operators/Ordering/Comparator.php | Value comparison/hashing |
| SumValues | Capabilities/Aggregate/SumValues.php | Operator (Aggregate) | Operators/Aggregate/SumValues.php | Sum aggregation |
| AverageValues | Capabilities/Aggregate/AverageValues.php | Operator (Aggregate) | Operators/Aggregate/AverageValues.php | Average aggregation |
| CountValues | Capabilities/Aggregate/CountValues.php | Operator (Aggregate) | Operators/Aggregate/CountValues.php | Count aggregation |
| FindMaxValue | Capabilities/Aggregate/FindMaxValue.php | Operator (Aggregate) | Operators/Aggregate/FindMaxValue.php | Find maximum |
| FindMinValue | Capabilities/Aggregate/FindMinValue.php | Operator (Aggregate) | Operators/Aggregate/FindMinValue.php | Find minimum |
| ContainsValue | Capabilities/Search/ContainsValue.php | Operator (Search) | Operators/Search/ContainsValue.php | Presence check |
| SearchValue | Capabilities/Search/SearchValue.php | Operator (Search) | Operators/Search/SearchValue.php | Search operation |
| MatchTextFuzzily | Capabilities/Search/MatchTextFuzzily.php | Operator (Search) | Operators/Search/MatchTextFuzzily.php | Fuzzy text match |
| MatchTextPartially | Capabilities/Search/MatchTextPartially.php | Operator (Search) | Operators/Search/MatchTextPartially.php | Partial text match |
| HasValue | Capabilities/Selection/HasValue.php | Operator (Selection) | Operators/Selection/HasValue.php | Presence check |
| ReadValueByPath | Capabilities/Selection/ReadValueByPath.php | Operator (Selection) | Operators/Selection/ReadValueByPath.php | Path selection |
| ArrayReader | Capabilities/Arrays/ArrayReader.php | Operator (Arrays) | Operators/Arrays/ArrayReader.php | Array read utility |
| ArrayWriter | Capabilities/Arrays/ArrayWriter.php | Operator (Arrays) | Operators/Arrays/ArrayWriter.php | Array write utility |

### Lenses (nested data focus)

| Class | Current Path | Category | Target | Reason |
|-------|-------------|----------|--------|--------|
| DotPath | Capabilities/DataPaths/DotPath.php | Lens (DataPath) | Lenses/DataPath/DotPath.php | Dot notation path parsing |

### Coercion (DTO system + object mapping)

| Class | Current Path | Category | Target | Reason |
|-------|-------------|----------|--------|--------|
| DataTransfer | Capabilities/DataTransfer/DataTransfer.php | Coercion (DtoSystem) | Coercion/DtoSystem/DataTransfer.php | DTO transfer engine |
| DataTransferResult | Capabilities/DataTransfer/DataTransferResult.php | Coercion (DtoSystem) | Coercion/DtoSystem/DataTransferResult.php | Transfer result |
| DataTransferFailure (root) | Capabilities/DataTransfer/DataTransferFailure.php | Coercion (DtoSystem) | Coercion/DtoSystem/DataTransferFailure.php | Transfer failure (merge with nested) |
| DataTransferFailure (nested) | Capabilities/DataTransfer/Capabilities/ErrorReporting/DataTransferFailure.php | Coercion (DtoSystem) | Coercion/DtoSystem/DataTransferFailure.php | DUPLICATE — merge or rename to DataTransferError |
| DataTransferViolation | Capabilities/DataTransfer/DataTransferViolation.php | Coercion (DtoSystem) | Coercion/DtoSystem/DataTransferViolation.php | Violation value object |
| DataTransferViolations | Capabilities/DataTransfer/DataTransferViolations.php | Coercion (DtoSystem) | Coercion/DtoSystem/DataTransferViolations.php | Violations collection |
| DataTransferException | Capabilities/DataTransfer/DataTransferException.php | Coercion (DtoSystem) | Coercion/DtoSystem/DataTransferException.php | Transfer exception |
| DataObject | Capabilities/DataTransfer/DataObject.php | Coercion (DtoSystem) | Coercion/DtoSystem/DataObject.php | Base DTO class |
| AbstractDTO | Capabilities/DataTransfer/Foundation/AbstractDTO.php | Coercion (DtoSystem) | Coercion/DtoSystem/AbstractDTO.php | Abstract DTO base |
| SerializeLegacyDTO | Capabilities/DataTransfer/Compatibility/SerializeLegacyDTO.php | Coercion (DtoSystem) | Coercion/DtoSystem/SerializeLegacyDTO.php | Legacy DTO compatibility |
| CastWith | Capabilities/DataTransfer/Capabilities/Attributes/CastWith.php | Coercion (DtoSystem) | Coercion/DtoSystem/Attributes/CastWith.php | Cast attribute |
| DefaultValue | Capabilities/DataTransfer/Capabilities/Attributes/DefaultValue.php | Coercion (DtoSystem) | Coercion/DtoSystem/Attributes/DefaultValue.php | Default value attribute |
| Hidden | Capabilities/DataTransfer/Capabilities/Attributes/Hidden.php | Coercion (DtoSystem) | Coercion/DtoSystem/Attributes/Hidden.php | Hidden field attribute |
| ListOf | Capabilities/DataTransfer/Capabilities/Attributes/ListOf.php | Coercion (DtoSystem) | Coercion/DtoSystem/Attributes/ListOf.php | List type attribute |
| MapFrom | Capabilities/DataTransfer/Capabilities/Attributes/MapFrom.php | Coercion (DtoSystem) | Coercion/DtoSystem/Attributes/MapFrom.php | Field mapping attribute |
| Optional | Capabilities/DataTransfer/Capabilities/Attributes/Optional.php | Coercion (DtoSystem) | Coercion/DtoSystem/Attributes/Optional.php | Optional field attribute |
| Required | Capabilities/DataTransfer/Capabilities/Attributes/Required.php | Coercion (DtoSystem) | Coercion/DtoSystem/Attributes/Required.php | Required field attribute |
| FieldInputName | Capabilities/DataTransfer/Capabilities/FieldMapping/FieldInputName.php | Coercion (DtoSystem) | Coercion/DtoSystem/FieldMapping/FieldInputName.php | Field name mapping |
| MapInputNameToField | Capabilities/DataTransfer/Capabilities/FieldMapping/MapInputNameToField.php | Coercion (DtoSystem) | Coercion/DtoSystem/FieldMapping/MapInputNameToField.php | Input→field mapping |
| ReadMappedInputName | Capabilities/DataTransfer/Capabilities/FieldMapping/ReadMappedInputName.php | Coercion (DtoSystem) | Coercion/DtoSystem/FieldMapping/ReadMappedInputName.php | Read mapped name |
| ValueCasterInterface | Capabilities/DataTransfer/Capabilities/ValueConversion/ValueCasterInterface.php | Coercion (DtoSystem) | Coercion/DtoSystem/ValueConversion/ValueCasterInterface.php | Caster interface |
| ValueConversionContext | Capabilities/DataTransfer/Capabilities/ValueConversion/ValueConversionContext.php | Coercion (DtoSystem) | Coercion/DtoSystem/ValueConversion/ValueConversionContext.php | Conversion context |
| DataTransferConfig | Capabilities/DataTransfer/Configuration/DataTransferConfig.php | Coercion (DtoSystem) | Coercion/DtoSystem/Configuration/DataTransferConfig.php | Transfer configuration |
| UnknownFieldPolicy | Capabilities/DataTransfer/Configuration/UnknownFieldPolicy.php | Coercion (DtoSystem) | Coercion/DtoSystem/Configuration/UnknownFieldPolicy.php | Unknown field policy |
| ObjectMapper | Capabilities/ObjectHandling/ObjectMapper.php | Coercion (ObjectMapper) | Coercion/ObjectMapper/ObjectMapper.php | Array→object mapping |
| DTOValidationException | Capabilities/ObjectHandling/DTO/DTOValidationException.php | Coercion (ObjectMapper) | Coercion/ObjectMapper/DTOValidationException.php | Validation exception |

### Validation (rules + validators)

| Class | Current Path | Category | Target | Reason |
|-------|-------------|----------|--------|--------|
| Validator | Capabilities/Validation/Validator.php | Validation (Rules) | Validation/Rules/Validator.php | Core validator |
| DatabaseValidator | Capabilities/Validation/DatabaseValidator.php | Validation (Rules) | Validation/Rules/DatabaseValidator.php | Database validator |
| EmailRule | Capabilities/Validation/Attributes/Rules/EmailRule.php | Validation (Rules) | Validation/Rules/EmailRule.php | Email validation rule |
| MinLengthRule | Capabilities/Validation/Attributes/Rules/MinLengthRule.php | Validation (Rules) | Validation/Rules/MinLengthRule.php | Min length rule |
| PasswordComplexityRule | Capabilities/Validation/Rules/PasswordComplexityRule.php | Validation (Rules) | Validation/Rules/PasswordComplexityRule.php | Password complexity rule |

### Values (domain value objects)

| Class | Current Path | Category | Target | Reason |
|-------|-------------|----------|--------|--------|
| Moment | Capabilities/DateTime/Moment.php | Value (Temporal) | Values/Temporal/Moment.php | Immutable datetime |
| Uuid | Capabilities/Identity/Uuid.php | Value (Identity) | Values/Identity/Uuid.php | UUID value object |
| Money | Capabilities/Money/Money.php | Value (Money) | Values/Money/Money.php | Money value object |
| Currency | Capabilities/Money/Currency.php | Value (Money) | Values/Money/Currency.php | Currency value object |
| Percentage | Capabilities/Numbers/Percentage.php | Value (Numeric) | Values/Numeric/Percentage.php | Percentage value object |

### Object Reading (capabilities, not flows)

| Class | Current Path | Category | Target | Reason |
|-------|-------------|----------|--------|--------|
| ReadDataObject | Capabilities/ObjectReading/ReadDataObject.php | Operator (ObjectReading) | Operators/ObjectReading/ReadDataObject.php | Object reading capability |
| NormalizeDataObjectValue | Capabilities/ObjectReading/NormalizeDataObjectValue.php | Operator (ObjectReading) | Operators/ObjectReading/NormalizeDataObjectValue.php | Value normalization |

### Foundation (neutral primitives and failures)

| Class | Current Path | Category | Target | Reason |
|-------|-------------|----------|--------|--------|
| MutationGuard | Foundation/Mutability/MutationGuard.php | Foundation (Mutability) | Foundation/Mutability/MutationGuard.php | Mutation safety — stays |
| MutationException | Foundation/Failure/MutationException.php | Foundation (Failure) | Foundation/Failure/MutationException.php | Mutation failure — stays |
| InvalidJson | Foundation/Failure/InvalidJson.php | Foundation (Failure) | Foundation/Failure/InvalidJson.php | JSON failure — stays |
| DataFailure | Foundation/Failure/DataFailure.php | Foundation (Failure) | Foundation/Failure/DataFailure.php | Base data failure — stays |
| DataException | Foundation/EXCEPTIONS/DataException.php | Foundation (Exception) | Foundation/Exception/DataException.php | Base exception — stays |
| InvalidFlowException | Foundation/EXCEPTIONS/InvalidFlowException.php | Foundation (Exception) | Foundation/Exception/InvalidFlowException.php | Flow exception — stays |
| InvalidValueException | Foundation/EXCEPTIONS/InvalidValueException.php | Foundation (Exception) | Foundation/Exception/InvalidValueException.php | Value exception — stays |
| NormalizedIterable | Foundation/Normalization/NormalizedIterable.php | Foundation (Normalization) | Foundation/Normalization/NormalizedIterable.php | Iterable normalization — stays |
| MakeCollection | Foundation/Normalization/MakeCollection.php | Foundation (Normalization) | Foundation/Normalization/MakeCollection.php | Collection factory — stays |
| WrapValue | Foundation/Normalization/WrapValue.php | Foundation (Normalization) | Foundation/Normalization/WrapValue.php | Value wrapper — stays |

Note: There are two `Foundation/` trees — one under `Capabilities/` (moved from previous reconciliation) and one at `System/Foundation/`. The `Capabilities/Foundation/` files are duplicates of `System/Foundation/` files. These need to be reconciled — all references should point to `System/Foundation/`.

### Flows (orchestration layer — no moves, import updates only)

| Class | Current Path | Category | Target | Reason |
|-------|-------------|----------|--------|--------|
| AverageValues | Flows/Aggregate/AverageValues.php | Flow (unchanged) | Flows/Aggregate/AverageValues.php | Orchestration — stays |
| MaxValue | Flows/Aggregate/MaxValue.php | Flow (unchanged) | Flows/Aggregate/MaxValue.php | Orchestration — stays |
| MinValue | Flows/Aggregate/MinValue.php | Flow (unchanged) | Flows/Aggregate/MinValue.php | Orchestration — stays |
| SumValues | Flows/Aggregate/SumValues.php | Flow (unchanged) | Flows/Aggregate/SumValues.php | Orchestration — stays |
| Batch | Flows/Batch/Batch.php | Flow (unchanged) | Flows/Batch/Batch.php | Orchestration — stays |
| CreateCollection | Flows/CreateCollection/CreateCollection.php | Flow (unchanged) | Flows/CreateCollection/CreateCollection.php | Orchestration — stays |
| LazySequence | Flows/LazySequence/LazySequence.php | Flow (unchanged) | Flows/LazySequence/LazySequence.php | Orchestration — stays |
| NormalizeData | Flows/Normalize/NormalizeData.php | Flow (unchanged) | Flows/Normalize/NormalizeData.php | Orchestration — stays |
| Pipe | Flows/Pipeline/Pipe.php | Flow (unchanged) | Flows/Pipeline/Pipe.php | Orchestration — stays |
| Pipeline | Flows/Pipeline/Pipeline.php | Flow (unchanged) | Flows/Pipeline/Pipeline.php | Orchestration — stays |
| ReadNestedValue | Flows/Read/ReadNestedValue.php | Flow (unchanged) | Flows/Read/ReadNestedValue.php | Orchestration — stays |
| ReadDataObject | Flows/ReadDataObject/ReadDataObject.php | Flow (unchanged) | Flows/ReadDataObject/ReadDataObject.php | Orchestration — stays |
| ReadDataObjectValues | Flows/ReadDataObject/ReadDataObjectValues.php | Flow (unchanged) | Flows/ReadDataObject/ReadDataObjectValues.php | Orchestration — stays |
| ReadVisibleDataFields | Flows/ReadDataObject/ReadVisibleDataFields.php | Flow (unchanged) | Flows/ReadDataObject/ReadVisibleDataFields.php | Orchestration — stays |
| NormalizeDataObjectValue | Flows/ReadDataObject/NormalizeDataObjectValue.php | Flow (unchanged) | Flows/ReadDataObject/NormalizeDataObjectValue.php | Orchestration — stays |
| ReadDataValue | Flows/ReadDataValue/ReadDataValue.php | Flow (unchanged) | Flows/ReadDataValue/ReadDataValue.php | Orchestration — stays |
| (SerializeDataObject flows) | Flows/SerializeDataObject/ | Flow (unchanged) | Flows/SerializeDataObject/ | Orchestration — stays |
| (Transform flows) | Flows/Transform/ | Flow (unchanged) | Flows/Transform/ | Orchestration — stays |
| TransformData | Flows/TransformData/TransformData.php | Flow (unchanged) | Flows/TransformData/TransformData.php | Orchestration — stays |
| Window | Flows/Window/Window.php | Flow (unchanged) | Flows/Window/Window.php | Orchestration — stays |
| WriteNestedValue | Flows/Write/WriteNestedValue.php | Flow (unchanged) | Flows/Write/WriteNestedValue.php | Orchestration — stays |
| WriteDataValue | Flows/WriteDataValue/WriteDataValue.php | Flow (unchanged) | Flows/WriteDataValue/WriteDataValue.php | Orchestration — stays |

### PublicSurface + Configuration (no moves)

| Class | Current Path | Category | Target | Reason |
|-------|-------------|----------|--------|--------|
| Data | PublicSurface/Data.php | PublicSurface (unchanged) | PublicSurface/Data.php | Entry point — stays |
| DataInterface | PublicSurface/DataInterface.php | PublicSurface (unchanged) | PublicSurface/DataInterface.php | Contract — stays |
| shortcuts | PublicSurface/shortcuts.php | PublicSurface (unchanged) | PublicSurface/shortcuts.php | Helpers — stays |
| RegisterDataDependencies | Configuration/RegisterDataDependencies.php | Configuration (unchanged) | Configuration/RegisterDataDependencies.php | DI assembly — stays |

### Additional structure files

| Class | Current Path | Category | Target | Reason |
|-------|-------------|----------|--------|--------|
| DataStructure | Capabilities/Structures/DataStructure.php | Structure (Foundation) | Structures/Foundation/DataStructure.php | Base data structure interface |
| OperationResult | Capabilities/Structures/OperationResult.php | Structure (Functional) | Structures/Functional/OperationResult.php | Operation result wrapper |
| Threshold | Capabilities/Structures/Threshold.php | Structure (Functional) | Structures/Functional/Threshold.php | Threshold value object |

## 3. Duplicate Path Implementations

### DotPath
Only one implementation exists: `Capabilities/DataPaths/DotPath.php`. No `ArrayPath` duplicate found. No `Collection/Internal/DotPath` duplicate (already cleaned in previous reconciliation).

### Foundation duplicates
Two Foundation trees exist:
- `System/Foundation/` — canonical foundation (EXCEPTIONS/, Failure/, Mutability/, Normalization/)
- `Capabilities/Foundation/` — moved during previous reconciliation (Mutability/, Normalization/)

Files in `Capabilities/Foundation/` are **duplicates** of `System/Foundation/`. All references should use `System/Foundation/`. The `Capabilities/Foundation/` directory should be removed and all imports updated to `System\Foundation\`.

### DataTransferFailure name conflict
- `Capabilities/DataTransfer/DataTransferFailure.php` (root)
- `Capabilities/DataTransfer/Capabilities/ErrorReporting/DataTransferFailure.php` (nested)

Both have the same class name `DataTransferFailure`. During Coercion move, the nested one should be renamed to `DataTransferError` or merged into the root one.

## 4. Remaining Collection/Internal-Style Shared Primitives

None. The previous composition reconciliation already eliminated `Collection/Internal/` and shared traits. Arrhae, Collection, and Json have no shared trait dependencies.

## 5. Existing Data Structures

| Structure | Exists | Path |
|-----------|--------|------|
| Map | Yes | Capabilities/Map/Map.php |
| Set | Yes | Capabilities/Set/Set.php |
| Sequence | Yes | Capabilities/Sequence/Sequence.php |
| OrderedMap | Yes | Capabilities/OrderedMap/OrderedMap.php |
| OrderedSet | Yes | Capabilities/OrderedSet/OrderedSet.php |
| MultiMap | Yes | Capabilities/MultiMap/MultiMap.php |
| DataList | Yes | Capabilities/DataList/DataList.php |
| Queue | No | — |
| Stack | No | — |
| Deque | No | — |
| PriorityQueue | No | — |
| Heap | No | — |
| Tree | No | — |
| Graph | No | — |
| UnionFind | No | — |
| Trie | No | — |
| MerkleTree | No | — |
| BiMap | No | — |
| SortedMap | No | — |
| SortedSet | No | — |
| MultiSet | No | — |
| RingBuffer | No | — |
| BitSet | No | — |
| Matrix | No | — |

## 6. Structures to Defer

All structures that do not exist are deferred. They are NOT created as placeholders.

**Deferred to CORE_LATER**: Queue, Stack, Deque, PriorityQueue, Heap, MinHeap, MaxHeap, Tree, BinaryTree, BinarySearchTree, Trie, Graph, DirectedGraph, WeightedGraph, UnionFind, BiMap, SortedMap, SortedSet, MultiSet, RingBuffer, BitSet, Matrix.

**Deferred to LABS_ONLY**: BloomFilter, CountMinSketch, HyperLogLog, Rope, PieceTable, MerkleTree, PersistentMap, PersistentSet, PersistentVector, CRDT structures, lock-free structures, compressed structures.

**NON_GOAL**: Custom database indexes, custom storage engines, custom distributed database structures, custom low-level memory allocators, production lock-free concurrency primitives.

## 7. Public Surface Entrypoints

| Current | Purpose | Planned |
|---------|---------|---------|
| `PublicSurface/Data.php` | Main facade over ArrayReader, ArrayWriter, Flows | Keep, update imports |
| `PublicSurface/DataInterface.php` | Contract for Data | Keep, update imports |
| `PublicSurface/shortcuts.php` | Empty stub | Keep or remove |
| — | Arrhae thin facade | Create → `PublicSurface/Arrhae.php` |
| — | Collection thin facade | Create → `PublicSurface/Collection.php` |
| — | Json thin facade | Create → `PublicSurface/Json.php` |
| — | Map thin facade | Create → `PublicSurface/Map.php` |
| — | Set thin facade | Create → `PublicSurface/Set.php` |
| — | Sequence thin facade | Create → `PublicSurface/Sequence.php` |

## 8. PHPStan/Test Risk Assessment

| Risk | Level | Impact | Mitigation |
|------|-------|--------|------------|
| Missed import update | High | Medium | PHPStan level 8 catches all |
| Flows reference old capability paths | High | High | Systematic grep + replace after capability moves |
| External components import Data classes | Medium | High | Search entire codebase before moves |
| Capabilities/Foundation duplicates System/Foundation | Medium | Medium | Remove Capabilities/Foundation/, update all refs |
| DataTransferFailure name conflict | Medium | Medium | Rename nested to DataTransferError during move |
| Test imports reference old paths | Low | Medium | Grep tests/ for old namespaces |
| PHPStan baseline stale refs | Low | Low | Regenerate baseline if needed |

**Current baseline:** PHPStan level 8 = 0 warnings, PHPUnit = 989 tests / 4003 assertions / 1 skipped, Autoload = 7049 classes, All governance checks = PASS.

## 9. Safe Migration Order

1. **Remove Capabilities/Foundation/ duplicates** → all refs to System/Foundation/
2. **Values** (Moment, Uuid, Money, Currency, Percentage) — no cross-references
3. **Structures** (Map, Set, Sequence, DataList, OrderedMap, OrderedSet, MultiMap, Pair, Tuple, Option, Result, Record, etc.) — depend on Foundation only
4. **Lenses** (DotPath) — depends on Foundation
5. **Operators** (Transform, Ordering, Aggregate, Search, Selection, Arrays, ObjectReading) — depend on Foundation, Lenses, Structures
6. **Shapes** (ClassShape, Visibility) — depend on Foundation, Operators
7. **Coercion** (DataTransfer→DtoSystem, ObjectMapper) — depend on Shapes, Foundation, external
8. **Validation** — depends on Foundation
9. **Forms** (Arrhae→ArrayForm, Collection→CollectionForm, Json→JsonForm) — depend on all Capabilities
10. **Flows** — import updates only, no moves
11. **PublicSurface** — import updates + new thin facades
12. **Configuration** — import updates only

## 10. Dependency Direction (to be proven by architecture tests)

```
PublicSurface → Forms, Flows, Operators, Structures
Flows → Forms, Operators, Structures, Foundation
Forms → Operators, Structures, Lenses, Foundation
Operators → Foundation, Lenses (some), Structures (some)
Structures → Foundation
Shapes → Foundation, Operators
Lenses → Foundation
Coercion → Shapes, Foundation, external Validation
Values → Foundation
Validation → Foundation
Foundation → nothing (neutral)
```

Forbidden directions:
- Arrhae → Collection or Json (must be independent)
- Operators → Forms
- Structures → Operators or Forms
- Foundation → any Capabilities
- Values → Operators or Forms
