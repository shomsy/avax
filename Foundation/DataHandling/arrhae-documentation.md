### Arrhae Documentation

---

#### Table of Contents

1. [Overview](#overview)
2. [Files and Their Purposes](#files-and-their-purposes)
    - [Arrhae.php](#arrhaephp)
        - [get](#get)
        - [set](#set)
        - [forget](#forget)
        - [toJson](#tojson)
    - [AbstractDependenciesTrait.php](#abstractdependenciestraitphp)
        - [getItems](#getitems)
        - [setItems](#setitems)
    - [AggregationTrait.php](#aggregationtraitphp)
        - [average](#average)
        - [sum](#sum)
        - [min](#min)
        - [max](#max)
        - [countBy](#countby)
        - [reduce](#reduce)
        - [aggregateGroupBy](#aggregategroupby)
    - [ArrayAccessTrait.php](#arrayaccesstraitphp)
        - [offsetExists](#offsetexists)
        - [offsetGet](#offsetget)
        - [offsetSet](#offsetset)
        - [offsetUnset](#offsetunset)
        - [getMultiple](#getmultiple)
        - [setMultiple](#setmultiple)
        - [pull](#pull)
        - [swap](#swap)
        - [keys](#keys)
        - [values](#values)
    - [ArrayConversionTrait.php](#arrayconversiontraitphp)
        - [toJson](#tojson)
        - [toArray](#toarray)
        - [toXml](#toxml)
        - [only](#only)
        - [except](#except)
        - [arrayToXml](#arraytoxml)
    - [CollectionWalkthroughTrait.php](#collectionwalkthroughtraitphp)
        - [map](#map)
        - [filter](#filter)
        - [each](#each)
        - [first](#first)
        - [zip](#zip)
        - [contains](#contains)
        - [indexOf](#indexof)
        - [search](#search)
        - [lastIndexOf](#lastindexof)
        - [where](#where)
        - [whereBetween](#wherebetween)
        - [whereIn](#wherein)
        - [whereNull](#wherenull)
        - [whereNotNull](#wherenotnull)
    - [ConditionalsTrait.php](#conditionalstraitphp)
        - [when](#when)
        - [unless](#unless)
        - [unlessEmpty](#unlessempty)
        - [whenNotEmpty](#whennotempty)
        - [isEmpty](#isempty)
        - [unlessNotEmpty](#unlessnotempty)
        - [whenEmpty](#whenempty)
    - [DebugTrait.php](#debugtraitphp)
        - [dump](#dump)
        - [dd](#dd)
        - [__debugInfo](#__debuginfo)
        - [debugInfo](#debuginfo)
        - [toArray](#toarray)
        - [count](#count)
    - [LazyEvaluationTrait.php](#lazyevaluationtraitphp)
        - [takeWhile](#takewhile)
        - [skipWhile](#skipwhile)
        - [nth](#nth)
        - [takeUntil](#takeuntil)
        - [skipUntil](#skipuntil)
        - [sliding](#sliding)
        - [take](#take)
        - [skip](#skip)
        - [toEager](#toeager)
    - [MacrosTrait.php](#macrostraitphp)
        - [macro](#macro)
        - [macroNamespace](#macronamespace)
        - [__callStatic](#__callstatic)
        - [__call](#__call)
    - [ManageItemsTrait.php](#manageitemstraitphp)
        - [append](#append)
        - [prepend](#prepend)
        - [concat](#concat)
        - [shift](#shift)
        - [pop](#pop)
        - [removeAt](#removeat)
        - [replaceAt](#replaceat)
        - [slice](#slice)
        - [all](#all)
    - [MetaInfoTrait.php](#metainfotraitphp)
        - [guid](#guid)
        - [timestamp](#timestamp)
        - [version](#version)
        - [clone](#clone)
    - [OrderManipulationTrait.php](#ordermanipulationtraitphp)
        - [sortAscending](#sortascending)
        - [shuffle](#shuffle)
    - [PartitioningTrait.php](#partitioningtraitphp)
        - [partition](#partition)
        - [groupBy](#groupby)
        - [split](#split)
        - [chunk](#chunk)
        - [all](#all-1)
    - [SetOperationsTrait.php](#setoperationstraitphp)
        - [intersect](#intersect)
        - [union](#union)
        - [diff](#diff)
        - [merge](#merge)
        - [symmetricDifference](#symmetricdifference)
    - [SortOperationsTrait.php](#sortoperationstraitphp)
        - [reverse](#reverse)
        - [sortBy](#sortby)
        - [sortDesc](#sortdesc)
        - [sortKeys](#sortkeys)
        - [sortKeysDesc](#sortkeysdesc)
        - [sortByMultiple](#sortbymultiple)
    - [StructureConversionTrait.php](#structureconversiontraitphp)
        - [dot](#dot)
        - [toList](#tolist)
        - [unDot](#undot)
    - [TransformationTrait.php](#transformationtraitphp)
        - [flatten](#flatten)
        - [flatMap](#flatmap)
        - [mapWithKeys](#mapwithkeys)
        - [transform](#transform)
        - [advancedTransform](#advancedtransform)
    - [AdvancedStringSearchTrait.php](#advancedstringsearchtraitphp)
        - [fuzzyMatch](#fuzzymatch)
        - [similaritySearch](#similaritysearch)
        - [levenshteinSearch](#levenshteinsearch)
        - [partialMatch](#partialmatch)
        - [tokenSortMatch](#tokensortmatch)
        - [tokenSetMatch](#tokensetmatch)
        - [phoneticMatch](#phoneticmatch)
        - [regexSearch](#regexsearch)
        - [customMatch](#custommatch)
        - [sortBySimilarity](#sortbysimilarity)
3. [Abstract Methods](#abstract-methods)
4. [Usage Scenarios](#usage-scenarios)
5. [Dependencies](#dependencies)
6. [Benefits](#benefits)
7. [Error Handling](#error-handling)
8. [Additional Notes](#additional-notes)
9. [Conclusion](#conclusion)

---

### Overview

The **Arrhae** class is an extension of PHP's native array functionality, designed for advanced data manipulation and
transformation. It provides a robust, chainable API inspired by modern collection libraries and incorporates numerous
traits to modularize and expand its capabilities. This documentation includes a detailed description of each file, its
purpose, methods, and practical real-world examples.

---

# Files and Their Purposes:

# Arrhae.php

**Purpose:**

The core class that combines all traits and provides advanced data handling capabilities, such as dot-notation access,
lazy evaluation, and method chaining.

**Key Responsibilities:**

- Acts as the entry point for all features.
- Implements foundational methods like `get`, `set`, `forget`, `toJson`, and more.
- Provides structure for integrating trait-based functionalities.

**Example:**

```
use App\Utils\Arrhae;

$arrh = new Arrhae(['user' => ['name' => 'Alice', 'role' => 'admin']]);

// Set a nested value
$arrh->set('user.status', 'active');

// Retrieve a nested value
echo $arrh->get('user.status'); // Output: 'active'
```

## get

##### get(string $key, mixed $default = null): mixed

- **Description:** Retrieves the value associated with the specified key. Supports dot-notation for accessing nested
  values.
- **Parameters:**
    - `string $key`: The key to retrieve.
    - `mixed $default`: The default value to return if the key does not exist.
- **Returns:** `mixed` - The value associated with the key or the default value.
- **Example:**

    ```
    $arrh = new Arrhae(['user' => ['name' => 'Alice']]);
    echo $arrh->get('user.name'); // Output: 'Alice'
    echo $arrh->get('user.age', 30); // Output: 30
    ```

##### set(string $key, mixed $value): static

- **Description:** Sets the value for the specified key. Supports dot-notation for setting nested values.
- **Parameters:**
    - `string $key`: The key to set.
    - `mixed $value`: The value to assign to the key.
- **Returns:** `static` - Returns the instance for method chaining.
- **Example:**

    ```
    $arrh = new Arrhae(['user' => ['name' => 'Alice']]);
    $arrh->set('user.age', 25);
    // Now $arrh contains ['user' => ['name' => 'Alice', 'age' => 25]]
    ```

##### forget(string|array $keys): static

- **Description:** Removes the specified key or keys from the collection. Supports dot-notation for removing nested
  keys.
- **Parameters:**
    - `string|array $keys`: The key or keys to remove.
- **Returns:** `static` - Returns the instance for method chaining.
- **Example:**

    ```
    $arrh = new Arrhae(['user' => ['name' => 'Alice', 'age' => 25]]);
    $arrh->forget('user.age');
    // Now $arrh contains ['user' => ['name' => 'Alice']]
    
    $arrh->forget(['user.name', 'user.status']);
    // Now $arrh contains ['user' => []]
    ```

##### toJson(int $options = 0): string

- **Description:** Converts the collection to a JSON string, with optional formatting.
- **Parameters:**
    - `int $options`: JSON encoding options (e.g., `JSON_PRETTY_PRINT`).
- **Returns:** `string` - JSON representation of the collection.
- **Throws:**
    - `InvalidArgumentException`: If the collection contains unencodable data.
- **Example:**

    ```
    $arrh = new Arrhae(['name' => 'Alice', 'age' => 25]);
    echo $arrh->toJson(JSON_PRETTY_PRINT);
    // Outputs:
    // {
    //     "name": "Alice",
    //     "age": 25
    // }
    ```

---

#### AbstractDependenciesTrait.php

**Purpose:**

This trait provides a foundational contract for collections by defining abstract methods for retrieving and setting
items. It ensures that any class using this trait implements methods to manage the underlying data structure.

**Key Responsibilities:**

- Establishes an abstract interface for data management in collections.
- Facilitates a consistent approach to handling collection items.

##### getItems(): array

- **Description:** Retrieves all items in the collection. This method is abstract and must be implemented by the using
  class.
- **Syntax:**

    ```
    abstract protected function getItems(): array;
    ```

- **Parameters:** None.
- **Returns:** `array` - An array of items in the collection.
- **Example Implementation:**

    ```
    protected function getItems(): array
    {
        return $this->items;
    }
    ```

##### setItems(array $items): static

- **Description:** Sets the collection's items to the provided array. This method is abstract and must be implemented by
  the using class.
- **Syntax:**

    ```
    abstract protected function setItems(array $items): static;
    ```

- **Parameters:**
    - `array $items`: The array of items to set in the collection.
- **Returns:** `static` - Returns the instance for method chaining.
- **Example Implementation:**

    ```
    protected function setItems(array $items): static
    {
        $this->items = $items;
        return $this;
    }
    ```

**Practical Use Case:**

This trait is commonly used in collection classes to ensure standardized access to internal data structures. It provides
flexibility for different implementations while maintaining a consistent interface.

---

#### AggregationTrait.php

**Purpose:**

The **AggregationTrait** provides a comprehensive set of methods for data aggregation in collections. It supports
operations like summation, averaging, finding minimum and maximum values, grouping, and counting occurrences. These
features make it an essential tool for working with large and complex data structures.

**Key Responsibilities:**

- Perform mathematical operations on collection items.
- Dynamically extract values using keys or callbacks.
- Provide utilities for grouping and counting unique values.

##### average(string|callable $key): float

- **Description:** Calculates the arithmetic mean of numeric values in the collection, extracted by a key or computed
  via a callback.
- **Parameters:**
    - `string|callable $key`: Key to extract values or callback to compute values.
- **Returns:** `float` - The average value or `0.0` if the collection is empty.
- **Throws:**
    - `InvalidArgumentException`: If non-numeric values are encountered.
    - `LogicException`: If the data structure is invalid.
- **Example:**

    ```
    $arrh = new Arrhae([
        ['score' => 80],
        ['score' => 90],
        ['score' => 70],
    ]);
    $average = $arrh->average('score'); // 80.0
    ```

##### sum(string|callable $key): float|int

- **Description:** Computes the sum of numeric values in the collection based on a key or callback.
- **Parameters:**
    - `string|callable $key`: Key or callback to compute values.
- **Returns:** `float|int` - The sum of values.
- **Example:**

    ```
    $arrh = new Arrhae([
        ['amount' => 100],
        ['amount' => 200],
    ]);
    $total = $arrh->sum('amount'); // 300
    ```

##### min(string|callable $key): mixed

- **Description:** Finds the smallest numeric value in the collection using a key or callback.
- **Parameters:**
    - `string|callable $key`: Key or callback for values.
- **Returns:** `mixed` - The minimum value.
- **Example:**

    ```
    $arrh = new Arrhae([
        ['score' => 50],
        ['score' => 20],
        ['score' => 90],
    ]);
    $minScore = $arrh->min('score'); // 20
    ```

##### max(string|callable $key): mixed

- **Description:** Finds the largest numeric value in the collection using a key or callback.
- **Parameters:**
    - `string|callable $key`: Key or callback for values.
- **Returns:** `mixed` - The maximum value.
- **Example:**

    ```
    $arrh = new Arrhae([
        ['score' => 50],
        ['score' => 20],
        ['score' => 90],
    ]);
    $maxScore = $arrh->max('score'); // 90
    ```

##### countBy(string|callable $key): array

- **Description:** Counts the occurrences of unique values in the collection based on a key or callback.
- **Parameters:**
    - `string|callable $key`: Key or callback for counting values.
- **Returns:** `array` - An associative array with counts for each unique value.
- **Example:**

    ```
    $arrh = new Arrhae([
        ['category' => 'A'],
        ['category' => 'B'],
        ['category' => 'A'],
    ]);
    $counts = $arrh->countBy('category');
    // ['A' => 2, 'B' => 1]
    ```

##### reduce(callable $callback, mixed $initial = null): mixed

- **Description:** Reduces the collection to a single value using a callback function.
- **Parameters:**
    - `callable $callback`: Callback with accumulator and current value.
    - `mixed|null $initial`: Initial value for the reduction.
- **Returns:** `mixed` - The reduced value.
- **Example:**

    ```
    $arrh = new Arrhae([1, 2, 3, 4]);
    $sum = $arrh->reduce(fn($carry, $item) => $carry + $item, 0); // 10
    ```

##### aggregateGroupBy(string|callable $key): array

- **Description:** Groups collection items based on a key or callback.
- **Parameters:**
    - `string|callable $key`: Key or callback for grouping.
- **Returns:** `array` - Grouped items.
- **Example:**

    ```
    $arrh = new Arrhae([
        ['category' => 'A', 'value' => 1],
        ['category' => 'B', 'value' => 2],
        ['category' => 'A', 'value' => 3],
    ]);
    $grouped = $arrh->aggregateGroupBy('category');
    // ['A' => [...], 'B' => [...]]
    ```

---

#### ArrayAccessTrait.php

**Purpose:**

The **ArrayAccessTrait** equips a class with array-like functionality, allowing collections to be interacted with as
though they were arrays. This includes checking, retrieving, setting, and unsetting individual or multiple items. The
trait uses the **AbstractDependenciesTrait** to ensure that the underlying data collection is properly managed.

**Key Responsibilities:**

- Implement array-like operations for collections.
- Facilitate batch operations (e.g., setting or retrieving multiple items).
- Provide utilities like swapping and pulling items.

##### offsetExists(mixed $offset): bool

- **Description:** Checks if a given offset exists in the collection.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $exists = $arrh->offsetExists(1); // true
    ```

##### offsetGet(mixed $offset): mixed

- **Description:** Retrieves the value at a specific offset. Returns `null` if the offset does not exist.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana']);
    echo $arrh->offsetGet(1); // 'banana'
    ```

##### offsetSet(mixed $offset, mixed $value): void

- **Description:** Sets a value at a specific offset or appends it if the offset is `null`.
- **Example:**

    ```
    $arrh = new Arrhae(['apple']);
    $arrh->offsetSet(null, 'banana'); // ['apple', 'banana']
    ```

##### offsetUnset(mixed $offset): void

- **Description:** Unsets a value at a specific offset in the collection.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana']);
    $arrh->offsetUnset(1); // ['apple']
    ```

##### getMultiple(array $keys): array

- **Description:** Retrieves values for an array of keys.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $values = $arrh->getMultiple([0, 2]); // ['apple', 'cherry']
    ```

##### setMultiple(array $values): void

- **Description:** Sets multiple values in the collection using an associative array.
- **Example:**

    ```
    $arrh = new Arrhae(['apple']);
    $arrh->setMultiple([1 => 'banana', 2 => 'cherry']);
    // ['apple', 'banana', 'cherry']
    ```

##### pull(mixed $offset): mixed

- **Description:** Retrieves and removes the value at a specific offset.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana']);
    $value = $arrh->pull(1); // 'banana', ['apple']
    ```

##### swap(mixed $offset1, mixed $offset2): void

- **Description:** Swaps values at two offsets.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana']);
    $arrh->swap(0, 1); // ['banana', 'apple']
    ```

##### keys(): array

- **Description:** Retrieves all keys from the collection.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana']);
    $keys = $arrh->keys(); // [0, 1]
    ```

##### values(): array

- **Description:** Retrieves all values from the collection.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana']);
    $values = $arrh->values(); // ['apple', 'banana']
    ```

---

#### ArrayConversionTrait.php

**Purpose:**

The **ArrayConversionTrait** provides methods for converting a collection into different formats, such as JSON, XML, and
plain arrays. It also includes utilities for filtering collections by including or excluding specific keys. This trait
is essential for exporting, transforming, or selectively modifying data.

**Key Responsibilities:**

- Convert collections to JSON, XML, or plain arrays.
- Provide key-based filtering operations (`only` and `except`).
- Ensure robust handling of nested structures and edge cases.

##### toJson(int $options = 0): string

- **Description:** Converts the collection to a JSON string, with optional formatting.
- **Parameters:**
    - `int $options`: JSON encoding options (e.g., `JSON_PRETTY_PRINT`).
- **Returns:** `string` - JSON representation of the collection.
- **Throws:**
    - `InvalidArgumentException`: If the collection contains unencodable data.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana']);
    echo $arrh->toJson(); // '["apple","banana"]'
    ```

##### toArray(): array

- **Description:** Recursively converts the collection and nested objects implementing `toArray()` to plain arrays.
- **Returns:** `array` - Array representation of the collection.
- **Example:**

    ```
    $arrh = new Arrhae([
        ['id' => 1, 'score' => 80],
        ['id' => 2, 'score' => 90],
    ]);
    $array = $arrh->toArray();
    // [['id' => 1, 'score' => 80], ['id' => 2, 'score' => 90]]
    ```

##### toXml(string $rootElement = 'root'): string

- **Description:** Converts the collection to an XML string with a customizable root element.
- **Parameters:**
    - `string $rootElement`: Name of the root element.
- **Returns:** `string` - XML representation of the collection.
- **Throws:**
    - `Exception`: If XML conversion fails.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana']);
    echo $arrh->toXml('fruits');
    // <fruits><item>apple</item><item>banana</item></fruits>
    ```

##### only(array $keys): static

- **Description:** Filters the collection to retain only the specified keys.
- **Parameters:**
    - `array $keys`: Keys to include.
- **Returns:** `static` - A new instance with only the specified keys.
- **Throws:**
    - `InvalidArgumentException`: If the keys array is empty.
- **Example:**

    ```
    $arrh = new Arrhae(['name' => 'Alice', 'age' => 25]);
    $filtered = $arrh->only(['name']);
    // ['name' => 'Alice']
    ```

##### except(array $keys): static

- **Description:** Filters the collection to exclude the specified keys.
- **Parameters:**
    - `array $keys`: Keys to exclude.
- **Returns:** `static` - A new instance without the excluded keys.
- **Throws:**
    - `InvalidArgumentException`: If the keys array is empty.
- **Example:**

    ```
    $arrh = new Arrhae(['name' => 'Alice', 'age' => 25]);
    $filtered = $arrh->except(['age']);
    // ['name' => 'Alice']
    ```

##### arrayToXml(array $data, SimpleXMLElement &$xml): void

- **Description:** Helper method to recursively convert an array to XML.
- **Parameters:**
    - `array $data`: Data to convert.
    - `SimpleXMLElement &$xml`: XML element to append data to.
- **Returns:** `void`.
- **Example:** Internal use only.

---

#### CollectionWalkthroughTrait.php

**Purpose:**

The **CollectionWalkthroughTrait** is a robust and versatile tool for traversing, querying, filtering, and searching
through collections. It provides methods for structured data handling and offers flexibility for complex collection
manipulations. This trait leverages **AbstractDependenciesTrait** for managing underlying data.

**Key Responsibilities:**

- Traverse collections with operations like `map`, `each`, and `filter`.
- Search collections with methods like `first`, `search`, and `contains`.
- Query and filter collections using `where`, `whereBetween`, `whereNull`, etc.
- Combine collections with methods like `zip`.

##### map(Closure $callback): static

- **Description:** Transforms collection items using a callback and returns a new collection.
- **Example:**

    ```
    $arrh = new Arrhae([1, 2, 3]);
    $squared = $arrh->map(fn($item) => $item ** 2);
    // [1, 4, 9]
    ```

##### filter(Closure $callback): static

- **Description:** Filters collection items based on a callback. Only items that satisfy the condition are included.
- **Example:**

    ```
    $arrh = new Arrhae([1, 2, 3, 4]);
    $evens = $arrh->filter(fn($item) => $item % 2 === 0);
    // [2, 4]
    ```

##### each(Closure $callback): void

- **Description:** Iterates over the collection, applying a callback without modifying items.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana']);
    $arrh->each(fn($item) => echo $item);
    // Outputs: applebanana
    ```

##### first(?Closure $callback = null): mixed

- **Description:** Retrieves the first item that matches a given condition or the first item if no condition is
  provided.
- **Example:**

    ```
    $arrh = new Arrhae([1, 2, 3]);
    $firstEven = $arrh->first(fn($item) => $item % 2 === 0);
    // 2
    ```

##### zip(array ...$items): static

- **Description:** Combines the collection with other arrays into grouped arrays (zip operation).
- **Example:**

    ```
    $arrh1 = new Arrhae([1, 2]);
    $arrh2 = new Arrhae(['a', 'b']);
    $zipped = $arrh1->zip($arrh2->getItems());
    // [[1, 'a'], [2, 'b']]
    ```

##### contains(mixed $value): bool

- **Description:** Checks if the collection contains a specific value.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana']);
    $hasBanana = $arrh->contains('banana');
    // true
    ```

##### indexOf(mixed $value): int|false

- **Description:** Alias for `search`. Returns the index of the first occurrence of a value.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana']);
    $index = $arrh->indexOf('banana');
    // 1
    ```

##### search(mixed $value, bool $strict = false): int|false

- **Description:** Finds the index of the first occurrence of a value.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana']);
    $index = $arrh->search('banana');
    // 1
    ```

##### lastIndexOf(mixed $value): int|false

- **Description:** Finds the index of the last occurrence of a value.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'apple']);
    $lastIndex = $arrh->lastIndexOf('apple');
    // 2
    ```

##### where(string $key, mixed $value): static

- **Description:** Filters items where a specific key matches a value.
- **Example:**

    ```
    $arrh = new Arrhae([['name' => 'Alice'], ['name' => 'Bob']]);
    $filtered = $arrh->where('name', 'Alice');
    // [['name' => 'Alice']]
    ```

##### whereBetween(string $key, array $range): static

- **Description:** Filters items where a key's value falls within a range.
- **Example:**

    ```
    $arrh = new Arrhae([['score' => 80], ['score' => 90]]);
    $filtered = $arrh->whereBetween('score', [85, 95]);
    // [['score' => 90]]
    ```

##### whereIn(string $key, array $values): static

- **Description:** Filters items where a key's value is in a given array.
- **Example:**

    ```
    $arrh = new Arrhae([['role' => 'admin'], ['role' => 'editor']]);
    $filtered = $arrh->whereIn('role', ['admin']);
    // [['role' => 'admin']]
    ```

##### whereNull(string $key): static

- **Description:** Filters items where a key's value is `null`.
- **Example:**

    ```
    $arrh = new Arrhae([['name' => 'Alice', 'age' => null]]);
    $filtered = $arrh->whereNull('age');
    // [['name' => 'Alice', 'age' => null]]
    ```

##### whereNotNull(string $key): static

- **Description:** Filters items where a key's value is not `null`.
- **Example:**

    ```
    $arrh = new Arrhae([['name' => 'Bob', 'age' => 30]]);
    $filtered = $arrh->whereNotNull('age');
    // [['name' => 'Bob', 'age' => 30]]
    ```

**Use Case Scenarios:**

- **Dynamic Query Modifications:**

    ```
    $arrh = new Arrhae([1, 2, 3]);
    $result = $arrh->when(true, fn($collection) => $collection->filter(fn($item) => $item > 1));
    // Result: [2, 3]
    ```

- **Fallback Defaults:**

    ```
    $arrh = new Arrhae([]);
    $result = $arrh->whenEmpty(fn($collection) => $collection->setItems(['default']));
    // Result: ['default']
    ```

- **Chained Logic:**

    ```
    $arrh = new Arrhae([1, 2, 3]);
    $result = $arrh
        ->when(true, fn($collection) => $collection->filter(fn($item) => $item > 1))
        ->unless(false, fn($collection) => $collection->map(fn($item) => $item * 2));
    // Result: [4, 6]
    ```

This trait provides a robust mechanism for embedding conditional logic directly within collection operations, leading to
cleaner and more maintainable code.

---

#### ConditionalsTrait.php

**Purpose:**

The **ConditionalsTrait** provides methods for conditionally applying callbacks to a collection based on specific
boolean conditions or the state of the collection (e.g., empty or not empty). These methods enhance the expressiveness
and readability of code when working with collections.

**Key Responsibilities:**

- Apply transformations or operations to collections conditionally.
- Enable chaining of conditional logic in a clean and readable manner.
- Support logical operations like `when`, `unless`, and their variants based on collection states.

##### when(bool $condition, Closure $callback): static

- **Description:** Executes a callback if the provided condition is `true`. The callback receives the current instance
  and must return it after modification.
- **Example:**

    ```
    $arrh = new Arrhae([1, 2, 3]);
    $result = $arrh->when(true, fn($collection) => $collection->filter(fn($item) => $item > 1));
    // Result: [2, 3]
    ```

##### unless(bool $condition, Closure $callback): static

- **Description:** Executes a callback if the provided condition is `false`.
- **Example:**

    ```
    $arrh = new Arrhae([1, 2, 3]);
    $result = $arrh->unless(false, fn($collection) => $collection->map(fn($item) => $item * 2));
    // Result: [2, 4, 6]
    ```

##### unlessEmpty(Closure $callback): static

- **Description:** Executes a callback unless the collection is empty.
- **Example:**

    ```
    $arrh = new Arrhae([1, 2]);
    $result = $arrh->unlessEmpty(fn($collection) => $collection->map(fn($item) => $item + 1));
    // Result: [2, 3]
    ```

##### whenNotEmpty(Closure $callback): static

- **Description:** Executes a callback if the collection is not empty.
- **Example:**

    ```
    $arrh = new Arrhae([1, 2]);
    $result = $arrh->whenNotEmpty(fn($collection) => $collection->map(fn($item) => $item * 2));
    // Result: [2, 4]
    ```

##### isEmpty(): bool

- **Description:** Checks if the collection contains no items.
- **Example:**

    ```
    $arrh = new Arrhae([]);
    $isEmpty = $arrh->isEmpty();
    // true
    ```

##### unlessNotEmpty(Closure $callback): static

- **Description:** Executes a callback unless the collection is not empty.
- **Example:**

    ```
    $arrh = new Arrhae([]);
    $result = $arrh->unlessNotEmpty(fn($collection) => $collection->setItems(['default']));
    // Result: ['default']
    ```

##### whenEmpty(Closure $callback): static

- **Description:** Executes a callback if the collection is empty.
- **Example:**

    ```
    $arrh = new Arrhae([]);
    $result = $arrh->whenEmpty(fn($collection) => $collection->setItems(['empty']));
    // Result: ['empty']
    ```

**Use Case Scenarios:**

- **Dynamic Query Modifications:**

    ```
    $arrh = new Arrhae([1, 2, 3]);
    $result = $arrh->when(true, fn($collection) => $collection->filter(fn($item) => $item > 1));
    // Result: [2, 3]
    ```

- **Fallback Defaults:**

    ```
    $arrh = new Arrhae([]);
    $result = $arrh->whenEmpty(fn($collection) => $collection->setItems(['default']));
    // Result: ['default']
    ```

- **Chained Logic:**

    ```
    $arrh = new Arrhae([1, 2, 3]);
    $result = $arrh
        ->when(true, fn($collection) => $collection->filter(fn($item) => $item > 1))
        ->unless(false, fn($collection) => $collection->map(fn($item) => $item * 2));
    // Result: [4, 6]
    ```

This trait provides a robust mechanism for embedding conditional logic directly within collection operations, leading to
cleaner and more maintainable code.

---

#### DebugTrait.php

**Purpose:**

The **DebugTrait** provides debugging functionalities for classes that manage collections. It enforces the
implementation of `toArray` and `count` methods in the using class, ensuring that the collection can be effectively
represented as an array and counted for debugging purposes.

**Key Responsibilities:**

- Output the current state of the collection in a human-readable format.
- Support debugging and development workflows with tools like `dump`, `dd`, and `__debugInfo`.
- Ensure consistency in debugging outputs through enforced method contracts (`toArray`, `count`).

##### dump(): static

- **Description:** Outputs the array representation of the collection using `var_dump` and returns the current instance
  for method chaining.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $arrh->dump();
    // Output:
    // array(3) {
    //   [0]=>
    //   string(5) "apple"
    //   [1]=>
    //   string(6) "banana"
    //   [2]=>
    //   string(6) "cherry"
    // }
    ```
- **Use Case:** Debugging the state of the collection without terminating the script.

##### dd(): void

- **Description:** Outputs the array representation of the collection using `var_dump` and terminates script execution.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $arrh->dd();
    // Output:
    // array(3) {
    //   [0]=>
    //   string(5) "apple"
    //   [1]=>
    //   string(6) "banana"
    //   [2]=>
    //   string(6) "cherry"
    // }
    // Script execution terminates.
    ```
- **Use Case:** Immediate debugging with termination to inspect the collection’s state during development.

##### __debugInfo(): array

- **Description:** Overrides the `__debugInfo` magic method to provide custom debugging information about the
  collection.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana']);
    var_dump($arrh);
    // Output:
    // array(2) {
    //   ["count"]=>
    //   int(2)
    //   ["items"]=>
    //   array(2) {
    //     [0]=>
    //     string(5) "apple"
    //     [1]=>
    //     string(6) "banana"
    //   }
    // }
    ```
- **Use Case:** Automatic integration with `var_dump` and other debugging tools.

##### debugInfo(): array

- **Description:** Provides a structured debugging output, including the count of items and their array representation.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $debugInfo = $arrh->debugInfo();
    // Returns:
    // [
    //     'count' => 3,
    //     'items' => ['apple', 'banana', 'cherry'],
    // ]
    ```
- **Use Case:** Logging or inspecting the state of the collection programmatically.

##### toArray(): array

- **Description:** Abstract method that must be implemented in the using class to convert the collection to an array.
- **Example Implementation:**

    ```
    public function toArray(): array {
        return $this->items;
    }
    ```
- **Use Case:** Enables all debugging methods to work seamlessly by ensuring a consistent array representation.

##### count(): int

- **Description:** Abstract method that must be implemented in the using class to return the number of items in the
  collection.
- **Example Implementation:**

    ```
    public function count(): int {
        return count($this->items);
    }
    ```
- **Use Case:** Provides the count for debugging purposes, ensuring compatibility with `debugInfo`.

**Use Case Scenarios:**

- **Inspecting Collection State:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $arrh->dump()->map(fn($item) => strtoupper($item));
    // Output:
    // array(3) {
    //   [0]=>
    //   string(5) "apple"
    //   [1]=>
    //   string(6) "banana"
    //   [2]=>
    //   string(6) "cherry"
    // }
    ```

- **Immediate Debugging with Termination:**

    ```
    $arrh = new Arrhae(['apple', 'banana']);
    $arrh->dd();
    ```

- **Custom Debugging Information:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $debug = $arrh->debugInfo();
    // Use $debug to log or monitor collection states.
    ```

**Advanced Examples:**

1. **Combined Debugging:**

    ```
    $arrh = new Arrhae([1, 2, 3]);
    $arrh->dump()->map(fn($item) => $item * 2)->dump();
    // Outputs the collection before and after transformation.
    ```

2. **Integration with Logging:**

    ```
    $arrh = new Arrhae(['key1' => 'value1', 'key2' => 'value2']);
    error_log(json_encode($arrh->debugInfo()));
    // Logs the collection’s state for analysis.
    ```

The **DebugTrait** ensures consistency and expressiveness in debugging workflows, making it an essential tool for
developers managing complex collections.

---

#### LazyEvaluationTrait.php

**Purpose:**

The **LazyEvaluationTrait** introduces memory-efficient operations to manipulate large collections of data. By utilizing
generator functions, it enables lazy evaluation, processing elements only when needed, which conserves memory and
improves performance.

**Key Responsibilities:**

- Provide methods for lazy traversal, filtering, and sampling of collection items.
- Enable operations such as skipping or taking items based on conditions, selecting every nth element, or sliding
  windows over collections.
- Support functional programming paradigms with closures for customized operations.

##### takeWhile(Closure $callback): static

- **Description:** Creates a new collection that takes items while a condition (defined by the callback) evaluates to
  `true`.
- **Example:**

    ```
    $arrh = new Arrhae([1, 2, 3, 4, 5]);
    $result = $arrh->takeWhile(fn($item) => $item < 4);
    // $result yields 1, 2, 3
    ```

##### skipWhile(Closure $callback): static

- **Description:** Creates a new collection that skips items while a condition evaluates to `true`.
- **Example:**

    ```
    $arrh = new Arrhae([1, 2, 3, 4, 5]);
    $result = $arrh->skipWhile(fn($item) => $item < 3);
    // $result yields 3, 4, 5
    ```

##### nth(int $step): static

- **Description:** Retrieves every nth item in the collection.
- **Example:**

    ```
    $arrh = new Arrhae([1, 2, 3, 4, 5, 6]);
    $result = $arrh->nth(2);
    // $result yields 1, 3, 5
    ```

##### takeUntil(Closure $callback): static

- **Description:** Creates a new collection that takes items until a condition evaluates to `true`.
- **Example:**

    ```
    $arrh = new Arrhae([1, 2, 3, 4, 5]);
    $result = $arrh->takeUntil(fn($item) => $item === 4);
    // $result yields 1, 2, 3
    ```

##### skipUntil(Closure $callback): static

- **Description:** Creates a new collection that skips items until a condition evaluates to `true`.
- **Example:**

    ```
    $arrh = new Arrhae([1, 2, 3, 4, 5]);
    $result = $arrh->skipUntil(fn($item) => $item === 3);
    // $result yields 3, 4, 5
    ```

##### sliding(int $size = 2, int $step = 1): static

- **Description:** Creates a sliding window of items over the collection.
- **Example:**

    ```
    $arrh = new Arrhae([1, 2, 3, 4, 5]);
    $result = $arrh->sliding(3, 1);
    // $result yields [1, 2, 3], [2, 3, 4], [3, 4, 5]
    ```

##### take(int $limit): static

- **Description:** Retrieves the first `limit` items from the collection.
- **Example:**

    ```
    $arrh = new Arrhae([1, 2, 3, 4, 5]);
    $result = $arrh->take(3);
    // $result yields 1, 2, 3
    ```

##### skip(int $offset): static

- **Description:** Skips the first `offset` items and retrieves the rest.
- **Example:**

    ```
    $arrh = new Arrhae([1, 2, 3, 4, 5]);
    $result = $arrh->skip(2);
    // $result yields 3, 4, 5
    ```

##### toEager(): static

- **Description:** Converts the lazy collection to an eager-loaded collection.
- **Example:**

    ```
    $lazyProducts = new Arrhae((function () {
        for ($i = 1; $i <= 1000; $i++) {
            yield ['id' => $i, 'name' => "Product $i"];
        }
    })());
    $eagerProducts = $lazyProducts->toEager();
    // Now all 1000 products are loaded into memory for immediate operations.
    ```

**Usage Notes:**

- **Efficiency:** Lazy evaluation allows processing large datasets without loading everything into memory.
- **Memory Management:** Only the required portion of the dataset is evaluated at any given time.
- **Chaining:** Methods can be chained to create complex pipelines for processing data.
- **Finalization:** Use `toEager()` to resolve all deferred operations into an eager-loaded collection when needed.

**Advanced Use Cases:**

1. **Combine Methods for Custom Pipelines:**

    ```
    $arrh = new Arrhae([1, 2, 3, 4, 5, 6]);
    $result = $arrh->skipWhile(fn($item) => $item < 3)
                   ->take(2)
                   ->nth(2);
    // $result yields 4
    ```

2. **Batch Processing with Sliding Windows:**

    ```
    $arrh = new Arrhae([1, 2, 3, 4, 5, 6]);
    $result = $arrh->sliding(3);
    foreach ($result as $window) {
        // Process $window (e.g., [1, 2, 3], [2, 3, 4], ...)
    }
    ```

3. **Lazy Pagination:**

    ```
    $arrh = new Arrhae(range(1, 1000));
    $page1 = $arrh->skip(0)->take(10);
    $page2 = $arrh->skip(10)->take(10);
    // Process paginated data lazily
    ```

By implementing lazy evaluation, **LazyEvaluationTrait** supports memory-efficient data handling, making it highly
suitable for large-scale collections and streaming scenarios.

---

#### MacrosTrait.php

**Purpose:**

The **MacrosTrait** adds dynamic extensibility to classes by allowing the registration and invocation of custom macros (
dynamic methods). These macros can be defined globally or within specific namespaces, enabling organized, reusable, and
flexible extensions for any class using this trait.

**Key Responsibilities:**

- Define and register global and namespaced macros dynamically.
- Handle magic method calls (`__call` and `__callStatic`) to execute macros.
- Enforce implementation of data-handling methods (`getItems` and `setItems`).

##### macro(string $name, Closure $macro): void

- **Description:** Registers a global macro with the given name and functionality.
- **Example:**

    ```
    MacrosTrait::macro('toUpperCase', function () {
        return array_map(fn($item) => strtoupper($item), $this->getItems());
    });
    
    $instance->toUpperCase(); // Converts all items to uppercase.
    ```

##### macroNamespace(string $namespace, string $name, Closure $macro): void

- **Description:** Registers a namespaced macro for better organization and to avoid naming conflicts.
- **Example:**

    ```
    MacrosTrait::macroNamespace('string', 'toCamelCase', function () {
        return array_map(
            fn($item) => lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $item)))),
            $this->getItems()
        );
    });
    
    $instance->string::toCamelCase(); // Converts items to camelCase format.
    ```

##### __callStatic(string $name, array $arguments)

- **Description:** Handles static calls to undefined methods. Executes registered macros (global or namespaced).
- **Example:**

    ```
    MacrosTrait::macro('staticSum', function () {
        return array_sum($this->getItems());
    });
    
    $sum = MyClass::staticSum(); // Sums all items in the collection statically.
    ```

##### __call(string $name, array $arguments)

- **Description:** Handles dynamic calls to undefined methods. Executes registered macros (global or namespaced).
- **Example:**

    ```
    MacrosTrait::macro('sum', function () {
        return array_sum($this->getItems());
    });
    
    $sum = $instance->sum(); // Sums all items in the collection dynamically.
    ```

**Abstract Methods:**

- **getItems(): iterable**

    - **Description:** Must be implemented by classes using the trait to provide the collection items.

- **setItems(iterable $items): static**

    - **Description:** Must be implemented by classes using the trait to update the collection items.

**Advanced Examples:**

1. **Dynamic Method Creation:**

    ```
    MacrosTrait::macro('filterPositive', function () {
        return array_filter($this->getItems(), fn($item) => $item > 0);
    });
    
    $instance = new MyCollection([-1, 2, -3, 4]);
    $positiveItems = $instance->filterPositive(); // Returns [2, 4]
    ```

2. **Namespaced Macros for Custom Calculations:**

    ```
    MacrosTrait::macroNamespace('math', 'average', function () {
        $items = $this->getItems();
        return array_sum($items) / count($items);
    });
    
    $instance = new MyCollection([10, 20, 30]);
    $average = $instance->math::average(); // Returns 20
    ```

3. **Static Macros:**

    ```
    MacrosTrait::macro('staticFindMax', function () {
        return max($this->getItems());
    });
    
    $max = MyCollection::staticFindMax(); // Returns the maximum value in the collection.
    ```

4. **Flexible Filtering with Namespaces:**

    ```
    MacrosTrait::macroNamespace('filter', 'odd', function () {
        return array_filter($this->getItems(), fn($item) => $item % 2 !== 0);
    });
    
    MacrosTrait::macroNamespace('filter', 'even', function () {
        return array_filter($this->getItems(), fn($item) => $item % 2 === 0);
    });
    
    $instance = new MyCollection([1, 2, 3, 4, 5]);
    $odds = $instance->filter::odd();  // Returns [1, 3, 5]
    $evens = $instance->filter::even(); // Returns [2, 4]
    ```

**Error Handling:**

- **Duplicate Macro Names:**
    - Throws an `InvalidArgumentException` if attempting to register a macro with an existing name.

- **Invalid Macro Usage:**
    - Throws a `BadMethodCallException` if an undefined macro is invoked.

**Conclusion:**

The **MacrosTrait** empowers developers to extend class functionality dynamically while maintaining organization and
flexibility. It is particularly useful in scenarios where the core functionality of a collection must adapt to varied
use cases without modifying the base class.

---

#### ManageItemsTrait.php

**Purpose:**

The **ManageItemsTrait** provides methods for managing collections of data. It enables classes to manipulate their
collections immutably, offering functionalities to append, prepend, concatenate, slice, replace, and remove items
efficiently.

**Key Responsibilities:**

- Enforce the implementation of `getItems` and `setItems` for data handling.
- Allow safe and immutable modifications to the collection.
- Offer utility methods for collection operations like appending, prepending, slicing, and more.

##### append(mixed $value): static

- **Description:** Appends a value to the end of the collection and returns a new instance.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana']);
    $newArrh = $arrh->append('cherry');
    // $newArrh contains ['apple', 'banana', 'cherry']
    ```

##### prepend(mixed $value): static

- **Description:** Prepends a value to the beginning of the collection and returns a new instance.
- **Example:**

    ```
    $arrh = new Arrhae(['banana', 'cherry']);
    $newArrh = $arrh->prepend('apple');
    // $newArrh contains ['apple', 'banana', 'cherry']
    ```

##### concat(iterable $items): static

- **Description:** Concatenates the current collection with another iterable and returns a new instance.
- **Example:**

    ```
    $arrh1 = new Arrhae(['apple', 'banana']);
    $arrh2 = new Arrhae(['cherry', 'date']);
    $concatenated = $arrh1->concat($arrh2);
    // $concatenated contains ['apple', 'banana', 'cherry', 'date']
    ```

##### shift(): static|null

- **Description:** Removes and returns the first item in the collection. Returns `null` if the collection is empty.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $newArrh = $arrh->shift();
    // $newArrh contains ['banana', 'cherry']
    ```

##### pop(): static|null

- **Description:** Removes and returns the last item in the collection. Returns `null` if the collection is empty.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $newArrh = $arrh->pop();
    // $newArrh contains ['apple', 'banana']
    ```

##### removeAt(int $index): static

- **Description:** Removes an item at a specific index and returns a new instance. Throws an exception if the index is
  invalid.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $newArrh = $arrh->removeAt(1);
    // $newArrh contains ['apple', 'cherry']
    ```

##### replaceAt(int $index, mixed $value): static

- **Description:** Replaces an item at a specific index with a new value and returns a new instance.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $newArrh = $arrh->replaceAt(1, 'blueberry');
    // $newArrh contains ['apple', 'blueberry', 'cherry']
    ```

##### slice(int $offset, ?int $length = null): static

- **Description:** Returns a sliced portion of the collection based on the offset and length.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry', 'date', 'elderberry']);
    $sliced = $arrh->slice(1, 3);
    // $sliced contains ['banana', 'cherry', 'date']
    ```

##### all(): array

- **Description:** Returns all items in the collection.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $allItems = $arrh->all();
    // $allItems contains ['apple', 'banana', 'cherry']
    ```

**Abstract Methods:**

- **getItems(): array**
    - **Description:** Must be implemented to return the collection's items.

- **setItems(array $items): static**
    - **Description:** Must be implemented to update the collection's items and return a new instance.

**Error Handling:**

- **Invalid Index:**
    - Throws `OutOfBoundsException` when an invalid index is accessed.

- **Negative Offset or Length:**
    - Throws `InvalidArgumentException` when offset or length in slice is negative.

**Conclusion:**

The **ManageItemsTrait** enhances a class with robust, immutable collection management capabilities. It ensures safe
data handling while providing a wide range of utility methods to manipulate collections effectively. This trait is ideal
for use in classes that prioritize immutability and memory efficiency.

---

#### MetaInfoTrait.php

**Purpose:**

The **MetaInfoTrait** provides methods to enrich items within a collection with metadata such as GUIDs, timestamps, and
version information. Additionally, it offers functionality for cloning collections, ensuring immutability and efficient
data handling. This trait enforces the implementation of `getItems()`, `setItems()`, `map()`, and `toArray()` methods in
the using class to enable proper manipulation of the underlying data collection.

**Methods:**

##### guid(): static

- **Description:** Enriches each item in the collection with a universally unique identifier (UUID) under the `id` key.
- **Returns:** `static` - A new instance with GUIDs added to each item.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $newArrh = $arrh->guid();
    // $newArrh contains:
    // [
    //     ['id' => 'uuid1', 'data' => 'apple'],
    //     ['id' => 'uuid2', 'data' => 'banana'],
    //     ['id' => 'uuid3', 'data' => 'cherry']
    // ]
    ```

##### timestamp(bool|null $set = null, string $format = 'U'): static

- **Description:** Sets or retrieves timestamps for items in the collection. Adds a `timestamp` key with the current
  time when `$set` is `true`, or retrieves the timestamps when `$set` is `false`.
- **Parameters:**
    - `$set` (`bool|null`): Whether to set timestamps (`true`) or retrieve them (`false`). Defaults to `true`.
    - `$format` (`string`): Optional date format for the timestamp. Defaults to Unix timestamp (`'U'`).
- **Returns:** `static` - A new instance containing the matched items sorted by similarity.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $timestamped = $arrh->timestamp();
    // $timestamped contains:
    // [
    //     ['timestamp' => 'current_timestamp', 'data' => 'apple'],
    //     ['timestamp' => 'current_timestamp', 'data' => 'banana'],
    //     ['timestamp' => 'current_timestamp', 'data' => 'cherry']
    // ]
    
    $timestamps = $arrh->timestamp(false);
    // $timestamps contains: ['current_timestamp', 'current_timestamp', 'current_timestamp']
    ```

##### version(int $version = 1): static

- **Description:** Adds version information to each item in the collection, with the `version` key indicating the
  assigned version number.
- **Parameters:**
    - `$version` (`int`): The version number to assign. Defaults to `1`.
- **Returns:** `static` - A new instance containing the version numbers.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $versioned = $arrh->version(2);
    // $versioned contains:
    // [
    //     ['version' => 2, 'data' => 'apple'],
    //     ['version' => 2, 'data' => 'banana'],
    //     ['version' => 2, 'data' => 'cherry']
    // ]
    ```

##### clone(): static

- **Description:** Creates a deep clone of the collection, returning a new instance with a copy of the current items.
- **Returns:** `static` - A cloned collection.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $cloned = $arrh->clone();
    // $cloned is a separate instance with the same items
    ```

**Abstract Methods:**

- **map(Closure $callback): static**

    - **Description:** Applies a callback to each item in the collection, transforming the items and returning a new
      instance with the transformed items.

- **getItems(): array**

    - **Description:** Retrieves the items in the collection.

- **setItems(array $items): static**

    - **Description:** Sets the items in the collection and returns a new instance.

- **toArray(): array**

    - **Description:** Converts the collection into an array representation.

**Usage Scenarios:**

- **Enriching collections with unique metadata for identification (`guid`).**
- **Adding timestamps to track modifications (`timestamp`).**
- **Applying versioning for API responses or data migrations (`version`).**
- **Creating clones of collections for immutable operations (`clone`).**

**Dependencies:**

- **Ramsey\Uuid:** For generating universally unique identifiers (UUIDs).
- **Carbon\Carbon:** For handling timestamps and date formatting.
- **AbstractDependenciesTrait:** Provides shared dependencies for data handling.
- **TransformationTrait:** Facilitates data transformation operations.

This trait is designed for scenarios where metadata management, versioning, and deep cloning are essential for robust
and traceable data collection operations.

---

#### OrderManipulationTrait.php

**Purpose:**

The **OrderManipulationTrait** provides methods for manipulating the order of arrays within a collection. It includes
functionality to sort items in ascending order based on a key or a callable function and to shuffle items randomly,
ensuring immutability.

**Key Responsibilities:**

- Provide methods for sorting and shuffling collections.
- Support both key-based and custom sorting criteria.
- Maintain immutability by returning new instances after operations.

##### sortAscending(string|callable $key): static

- **Description:** Sorts the collection items in ascending order based on a specified key or a custom comparison
  function.
- **Parameters:**
    - `$key` (`string|callable`): The key to sort by (for associative arrays) or a custom callable function for item
      comparison.
- **Returns:** `static` - A new instance with sorted items.
- **Throws:**
    - `InvalidArgumentException` if:
        - `$key` is a string, and the key does not exist in one or more items.
        - `$key` is not a valid string or callable.
- **Example:**

    ```
    $arrh = new Arrhae([
        ['name' => 'banana', 'price' => 1.2],
        ['name' => 'apple', 'price' => 0.8],
        ['name' => 'cherry', 'price' => 2.5],
    ]);
    
    // Sort by key 'name'
    $sorted = $arrh->sortAscending('name');
    // $sorted contains:
    // [
    //     ['name' => 'apple', 'price' => 0.8],
    //     ['name' => 'banana', 'price' => 1.2],
    //     ['name' => 'cherry', 'price' => 2.5],
    // ]
    
    // Sort using a custom callable
    $sortedByPrice = $arrh->sortAscending(fn($a, $b) => $a['price'] <=> $b['price']);
    // $sortedByPrice contains:
    // [
    //     ['name' => 'apple', 'price' => 0.8],
    //     ['name' => 'banana', 'price' => 1.2],
    //     ['name' => 'cherry', 'price' => 2.5],
    // ]
    ```

##### shuffle(): static

- **Description:** Randomizes the order of items in the collection.
- **Returns:** `static` - A new instance with shuffled items.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $shuffled = $arrh->shuffle();
    // $shuffled might contain ['cherry', 'apple', 'banana']
    ```

**Abstract Methods:**

- **getItems(): array**
    - **Description:** Retrieves the current collection of items as an array.

**Usage Scenarios:**

- **Sorting:** Use `sortAscending` to arrange items in a specific order based on a key or a custom logic. Ideal for
  ordered display or data processing tasks.
- **Randomization:** Use `shuffle` for scenarios requiring randomized order, such as random sampling, games, or testing.

**Dependencies:**

- **AbstractDependenciesTrait:** Provides shared functionalities required for dependency management.

**Benefits:**

- **Immutability:** Ensures the original collection remains unchanged by returning a new instance after every operation.
- **Flexibility:** Supports both key-based sorting and custom sorting logic via callables.
- **Simplicity:** Offers intuitive methods for common array order manipulations.

This trait is particularly useful for classes that handle collections requiring sorting and randomization of data,
ensuring flexibility and robustness in data handling operations.

---

#### PartitioningTrait.php

**Purpose:**

The **PartitioningTrait** provides methods for dividing, grouping, and chunking collections of data. This trait is
designed for scenarios requiring partitioning based on conditions, grouping items by specific keys or callback logic,
and dividing collections into specified groups or chunks. It ensures flexibility and immutability in data manipulation.

The trait enforces the implementation of the `getItems()` and `setItems()` methods by the using class to handle the
underlying data collection.

**Methods:**

##### partition(Closure $callback): array

- **Description:** Splits the collection into two groups based on a callback. Items satisfying the callback condition
  are placed in one group, and the remaining items in another.
- **Parameters:**
    - `$callback` (`Closure`): A callback function that determines the partition condition.
- **Returns:** `array` - An array containing two new collections:
    - The first collection contains items matching the condition.
    - The second collection contains items not matching the condition.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry', 'date']);
    [$fruitsWithA, $fruitsWithoutA] = $arrh->partition(fn($item) => strpos($item, 'a') !== false);
    // $fruitsWithA contains ['apple', 'banana', 'date']
    // $fruitsWithoutA contains ['cherry']
    ```

##### groupBy(Closure|string $key): static

- **Description:** Groups items in the collection by a specific key or the result of a callback function.
- **Parameters:**
    - `$key` (`Closure|string`): A key for grouping or a callback function that returns the group key for each item.
- **Returns:** `static` - A new collection where each group is a sub-collection.
- **Throws:**
    - `InvalidArgumentException` if a string key is provided and one or more items do not contain the key.
- **Example:**

    ```
    $arrh = new Arrhae([
        ['type' => 'fruit', 'name' => 'apple'],
        ['type' => 'fruit', 'name' => 'banana'],
        ['type' => 'vegetable', 'name' => 'carrot'],
    ]);
    
    // Group by a string key
    $grouped = $arrh->groupBy('type');
    // $grouped contains:
    // [
    //     'fruit' => new Arrhae([
    //         ['type' => 'fruit', 'name' => 'apple'],
    //         ['type' => 'fruit', 'name' => 'banana'],
    //     ]),
    //     'vegetable' => new Arrhae([
    //         ['type' => 'vegetable', 'name' => 'carrot'],
    //     ]),
    // ]
    
    // Group by a callback
    $groupedByLength = $arrh->groupBy(fn($item) => strlen($item['name']));
    // $groupedByLength contains:
    // [
    //     5 => new Arrhae([['type' => 'fruit', 'name' => 'apple']]),
    //     6 => new Arrhae([['type' => 'fruit', 'name' => 'banana'], ['type' => 'vegetable', 'name' => 'carrot']]),
    // ]
    ```

##### split(int $numberOfGroups): static

- **Description:** Divides the collection into a specified number of groups as evenly as possible.
- **Parameters:**
    - `$numberOfGroups` (`int`): The number of groups to create.
- **Returns:** `static` - A collection containing the specified number of groups as sub-collections.
- **Throws:**
    - `InvalidArgumentException` if `$numberOfGroups` is less than `1`.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry', 'date', 'elderberry']);
    $groups = $arrh->split(2);
    // $groups contains:
    // [
    //     new Arrhae(['apple', 'banana', 'cherry']),
    //     new Arrhae(['date', 'elderberry']),
    // ]
    ```

##### chunk(int $size): static

- **Description:** Splits the collection into chunks of a specified size.
- **Parameters:**
    - `$size` (`int`): The number of items in each chunk.
- **Returns:** `static` - A collection containing chunks as sub-collections.
- **Throws:**
    - `InvalidArgumentException` if `$size` is less than `1`.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry', 'date', 'elderberry']);
    $chunks = $arrh->chunk(2);
    // $chunks contains:
    // [
    //     new Arrhae(['apple', 'banana']),
    //     new Arrhae(['cherry', 'date']),
    //     new Arrhae(['elderberry']),
    // ]
    ```

##### all(): array

- **Description:** Returns all items in the collection.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $allItems = $arrh->all();
    // $allItems contains ['apple', 'banana', 'cherry']
    ```

**Abstract Methods:**

- **getItems(): array**
    - **Description:** Retrieves the current collection of items as an array.

- **setItems(array $items): static**
    - **Description:** Replaces the current collection with a new set of items and returns a new instance.

**Usage Scenarios:**

- **Partitioning:** Use `partition` for dividing collections based on conditions, such as separating items by a specific
  property or value.
- **Grouping:** Use `groupBy` to organize collections into categories for analysis, display, or data processing.
- **Splitting:** Use `split` for dividing data evenly, such as paginating results or distributing workloads.
- **Chunking:** Use `chunk` for batching items, especially in scenarios involving iterative processing.

**Benefits:**

- **Flexibility:** Supports both key-based and callback-based grouping and partitioning.
- **Immutability:** Ensures the original collection remains unchanged, returning new instances for each operation.
- **Efficiency:** Provides structured methods for working with large collections, improving readability and
  maintainability.

This trait is ideal for scenarios requiring the organization and partitioning of collections into meaningful structures
for processing, analysis, or display.

---

#### SetOperationsTrait.php

**Purpose:**

The **SetOperationsTrait** provides methods for performing common set operations on collections, such as intersection,
union, difference, merging, and symmetric difference. These operations allow for advanced manipulation and comparison of
collections while maintaining immutability.

The trait enforces the implementation of the `getItems()` and `setItems()` methods by the using class to handle the
underlying data.

**Methods:**

##### intersect(self $collection): static

- **Description:** Computes the intersection of the current collection and the provided collection, returning only the
  items that exist in both.
- **Parameters:**
    - `$collection` (`self`): The collection to intersect with.
- **Returns:** `static` - A new collection containing the intersected items.
- **Throws:**
    - `InvalidArgumentException` if the provided collection is empty.
- **Example:**

    ```
    $arrh1 = new Arrhae(['apple', 'banana', 'cherry']);
    $arrh2 = new Arrhae(['banana', 'cherry', 'date']);
    $intersection = $arrh1->intersect($arrh2);
    // $intersection contains ['banana', 'cherry']
    ```

##### union(self $collection): static

- **Description:** Returns the union of the current collection and the provided collection, ensuring uniqueness of
  items.
- **Parameters:**
    - `$collection` (`self`): The collection to union with.
- **Returns:** `static` - A new collection containing all unique items from both collections.
- **Example:**

    ```
    $arrh1 = new Arrhae(['apple', 'banana']);
    $arrh2 = new Arrhae(['banana', 'cherry']);
    $union = $arrh1->union($arrh2);
    // $union contains ['apple', 'banana', 'cherry']
    ```

##### diff(self $collection): static

- **Description:** Computes the difference between the current collection and the provided collection, returning items
  that exist in the current collection but not in the provided one.
- **Parameters:**
    - `$collection` (`self`): The collection to compare against.
- **Returns:** `static` - A new collection containing the difference of items.
- **Example:**

    ```
    $arrh1 = new Arrhae(['apple', 'banana', 'cherry']);
    $arrh2 = new Arrhae(['banana', 'date']);
    $difference = $arrh1->diff($arrh2);
    // $difference contains ['apple', 'cherry']
    ```

##### merge(self $collection): static

- **Description:** Merges the current collection with the provided collection, combining all items without ensuring
  uniqueness.
- **Parameters:**
    - `$collection` (`self`): The collection to merge with.
- **Returns:** `static` - A new collection with merged items.
- **Example:**

    ```
    $arrh1 = new Arrhae(['apple', 'banana']);
    $arrh2 = new Arrhae(['cherry', 'date']);
    $merged = $arrh1->merge($arrh2);
    // $merged contains ['apple', 'banana', 'cherry', 'date']
    ```

##### symmetricDifference(self $collection): static

- **Description:** Computes the symmetric difference between two collections, returning items that are in either
  collection but not in both.
- **Parameters:**
    - `$collection` (`self`): The collection to compare against.
- **Returns:** `static` - A new collection with the symmetric difference of items.
- **Throws:**
    - `InvalidArgumentException` if the provided collection is invalid or contains incompatible types.
- **Example:**

    ```
    $arrh1 = new Arrhae(['apple', 'banana', 'cherry']);
    $arrh2 = new Arrhae(['banana', 'date', 'fig']);
    $symDifference = $arrh1->symmetricDifference($arrh2);
    // $symDifference contains ['apple', 'cherry', 'date', 'fig']
    ```

**Abstract Methods:**

- **getItems(): array**
    - **Description:** Retrieves the current collection of items as an array.

- **setItems(array $items): static**
    - **Description:** Replaces the current collection with a new set of items and returns a new instance.

**Usage Scenarios:**

- **Intersection:** Use `intersect` to find common elements between two datasets.
- **Union:** Use `union` to combine datasets into a single collection without duplicates.
- **Difference:** Use `diff` to identify unique elements in one dataset compared to another.
- **Merging:** Use `merge` to concatenate datasets without checking for duplicates.
- **Symmetric Difference:** Use `symmetricDifference` to find items exclusive to each dataset.

**Benefits:**

- **Flexibility:** Supports common set operations with intuitive methods.
- **Immutability:** Returns new instances for each operation, preserving the original collection.
- **Consistency:** Handles collections of various types consistently using the `toArray()` method.

This trait is ideal for applications requiring mathematical or logical operations on collections, such as managing
overlapping datasets, finding unique items, or combining multiple datasets efficiently.

---

#### SortOperationsTrait.php

**Purpose:**

The **SortOperationsTrait** provides robust and flexible sorting functionalities for collections. It allows for sorting
by keys or custom criteria, reversing the order of items, and sorting by multiple criteria. The trait is designed to
work seamlessly in real-world applications where complex data structures require advanced sorting logic.

This trait requires implementing classes to define `getItems()` and `setItems()` methods for accessing and updating the
underlying collection.

**Requirements:**

Classes using this trait must implement:

- **getItems(): array**
    - **Description:** Retrieves the current collection of items.

- **setItems(array $items): static**
    - **Description:** Updates the collection and returns a new instance.

**Provided Methods:**

##### reverse(): static

- **Description:** Reverses the order of the collection.
- **Real-World Example:**

    ```
    $orders = new Arrhae([
        ['id' => 1, 'amount' => 100, 'date' => '2024-01-01'],
        ['id' => 2, 'amount' => 150, 'date' => '2024-01-02'],
        ['id' => 3, 'amount' => 200, 'date' => '2024-01-03'],
    ]);
    $reversedOrders = $orders->reverse();
    // Result:
    // [
    //     ['id' => 3, 'amount' => 200, 'date' => '2024-01-03'],
    //     ['id' => 2, 'amount' => 150, 'date' => '2024-01-02'],
    //     ['id' => 1, 'amount' => 100, 'date' => '2024-01-01'],
    // ]
    ```

##### sortBy(Closure|string $key): static

- **Description:** Sorts items by a specified key or custom comparison function in ascending order.
- **Real-World Example:**

    ```
    $products = new Arrhae([
        ['name' => 'Laptop', 'price' => 1200, 'stock' => 30],
        ['name' => 'Mouse', 'price' => 20, 'stock' => 200],
        ['name' => 'Keyboard', 'price' => 50, 'stock' => 100],
    ]);
    $sortedProducts = $products->sortBy('price');
    // Result:
    // [
    //     ['name' => 'Mouse', 'price' => 20, 'stock' => 200],
    //     ['name' => 'Keyboard', 'price' => 50, 'stock' => 100],
    //     ['name' => 'Laptop', 'price' => 1200, 'stock' => 30],
    // ]
    ```

##### sortDesc(Closure|string $key): static

- **Description:** Sorts items by a specified key or custom comparison function in descending order.
- **Real-World Example:**

    ```
    $users = new Arrhae([
        ['name' => 'Alice', 'lastLogin' => '2024-12-15'],
        ['name' => 'Bob', 'lastLogin' => '2024-12-10'],
        ['name' => 'Charlie', 'lastLogin' => '2024-12-20'],
    ]);
    $sortedUsers = $users->sortDesc('lastLogin');
    // Result:
    // [
    //     ['name' => 'Charlie', 'lastLogin' => '2024-12-20'],
    //     ['name' => 'Alice', 'lastLogin' => '2024-12-15'],
    //     ['name' => 'Bob', 'lastLogin' => '2024-12-10'],
    // ]
    ```

##### sortKeys(): static

- **Description:** Sorts the collection by its keys in ascending order.
- **Real-World Example:**

    ```
    $inventory = new Arrhae([
        'C3' => ['item' => 'Cables', 'quantity' => 50],
        'A1' => ['item' => 'Adapters', 'quantity' => 100],
        'B2' => ['item' => 'Batteries', 'quantity' => 75],
    ]);
    $sortedInventory = $inventory->sortKeys();
    // Result:
    // [
    //     'A1' => ['item' => 'Adapters', 'quantity' => 100],
    //     'B2' => ['item' => 'Batteries', 'quantity' => 75],
    //     'C3' => ['item' => 'Cables', 'quantity' => 50],
    // ]
    ```

##### sortKeysDesc(): static

- **Description:** Sorts the collection by its keys in descending order.
- **Real-World Example:**

    ```
    $logs = new Arrhae([
        'log3' => ['level' => 'error', 'message' => 'System failure'],
        'log1' => ['level' => 'info', 'message' => 'Application started'],
        'log2' => ['level' => 'warning', 'message' => 'High memory usage'],
    ]);
    $sortedLogs = $logs->sortKeysDesc();
    // Result:
    // [
    //     'log3' => ['level' => 'error', 'message' => 'System failure'],
    //     'log2' => ['level' => 'warning', 'message' => 'High memory usage'],
    //     'log1' => ['level' => 'info', 'message' => 'Application started'],
    // ]
    ```

##### sortByMultiple(array $criteria): static

- **Description:** Sorts the collection by multiple criteria with specified orders.
- **Real-World Example:**

    ```
    $employees = new Arrhae([
        ['name' => 'Alice', 'department' => 'HR', 'salary' => 50000],
        ['name' => 'Bob', 'department' => 'IT', 'salary' => 60000],
        ['name' => 'Charlie', 'department' => 'HR', 'salary' => 55000],
        ['name' => 'Dave', 'department' => 'IT', 'salary' => 50000],
    ]);
    $sortedEmployees = $employees->sortByMultiple([
        'department' => 'asc',
        'salary' => 'desc',
    ]);
    // Result:
    // [
    //     ['name' => 'Charlie', 'department' => 'HR', 'salary' => 55000],
    //     ['name' => 'Alice', 'department' => 'HR', 'salary' => 50000],
    //     ['name' => 'Bob', 'department' => 'IT', 'salary' => 60000],
    //     ['name' => 'Dave', 'department' => 'IT', 'salary' => 50000],
    // ]
    ```

**Exception Handling:**

- **InvalidArgumentException:**
    - Thrown when a key does not exist in one or more items for sorting.

**Usage Notes:**

- **Immutability:** All methods return a new instance, preserving the original collection.
- **Flexible Sorting:** Methods support both key-based and custom callback sorting.
- **Multi-Criteria Sorting:** Allows for granular control over sorting logic, making it suitable for real-world business
  cases.

This trait is particularly useful for classes that handle collections requiring sorting and randomization of data,
ensuring flexibility and robustness in data handling operations.

---

#### PartitioningTrait.php

**Overview:**

The **PartitioningTrait** provides methods for dividing, grouping, and chunking collections of data. This trait is
designed for scenarios requiring partitioning based on conditions, grouping items by specific keys or callback logic,
and dividing collections into specified groups or chunks. It ensures flexibility and immutability in data manipulation.

The trait enforces the implementation of the `getItems()` and `setItems()` methods by the using class to handle the
underlying data collection.

**Methods:**

##### partition(Closure $callback): array

- **Description:** Splits the collection into two groups based on a callback. Items satisfying the callback condition
  are placed in one group, and the remaining items in another.
- **Parameters:**
    - `$callback` (`Closure`): A callback function that determines the partition condition.
- **Returns:** `array` - An array containing two new collections:
    - The first collection contains items matching the condition.
    - The second collection contains items not matching the condition.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry', 'date']);
    [$fruitsWithA, $fruitsWithoutA] = $arrh->partition(fn($item) => strpos($item, 'a') !== false);
    // $fruitsWithA contains ['apple', 'banana', 'date']
    // $fruitsWithoutA contains ['cherry']
    ```

##### groupBy(Closure|string $key): static

- **Description:** Groups items in the collection by a specific key or the result of a callback function.
- **Parameters:**
    - `$key` (`Closure|string`): A key for grouping or a callback function that returns the group key for each item.
- **Returns:** `static` - A new collection where each group is a sub-collection.
- **Throws:**
    - `InvalidArgumentException` if a string key is provided and one or more items do not contain the key.
- **Example:**

    ```
    $arrh = new Arrhae([
        ['type' => 'fruit', 'name' => 'apple'],
        ['type' => 'fruit', 'name' => 'banana'],
        ['type' => 'vegetable', 'name' => 'carrot'],
    ]);
    
    // Group by a string key
    $grouped = $arrh->groupBy('type');
    // $grouped contains:
    // [
    //     'fruit' => new Arrhae([
    //         ['type' => 'fruit', 'name' => 'apple'],
    //         ['type' => 'fruit', 'name' => 'banana'],
    //     ]),
    //     'vegetable' => new Arrhae([
    //         ['type' => 'vegetable', 'name' => 'carrot'],
    //     ]),
    // ]
    
    // Group by a callback
    $groupedByLength = $arrh->groupBy(fn($item) => strlen($item['name']));
    // $groupedByLength contains:
    // [
    //     5 => new Arrhae([['type' => 'fruit', 'name' => 'apple']]),
    //     6 => new Arrhae([['type' => 'fruit', 'name' => 'banana'], ['type' => 'vegetable', 'name' => 'carrot']]),
    // ]
    ```

##### split(int $numberOfGroups): static

- **Description:** Divides the collection into a specified number of groups as evenly as possible.
- **Parameters:**
    - `$numberOfGroups` (`int`): The number of groups to create.
- **Returns:** `static` - A collection containing the specified number of groups as sub-collections.
- **Throws:**
    - `InvalidArgumentException` if `$numberOfGroups` is less than `1`.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry', 'date', 'elderberry']);
    $groups = $arrh->split(2);
    // $groups contains:
    // [
    //     new Arrhae(['apple', 'banana', 'cherry']),
    //     new Arrhae(['date', 'elderberry']),
    // ]
    ```

##### chunk(int $size): static

- **Description:** Splits the collection into chunks of a specified size.
- **Parameters:**
    - `$size` (`int`): The number of items in each chunk.
- **Returns:** `static` - A collection containing chunks as sub-collections.
- **Throws:**
    - `InvalidArgumentException` if `$size` is less than `1`.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry', 'date', 'elderberry']);
    $chunks = $arrh->chunk(2);
    // $chunks contains:
    // [
    //     new Arrhae(['apple', 'banana']),
    //     new Arrhae(['cherry', 'date']),
    //     new Arrhae(['elderberry']),
    // ]
    ```

##### all(): array

- **Description:** Returns all items in the collection.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $allItems = $arrh->all();
    // $allItems contains ['apple', 'banana', 'cherry']
    ```

**Abstract Methods:**

- **getItems(): array**
    - **Description:** Retrieves the current collection of items as an array.

- **setItems(array $items): static**
    - **Description:** Replaces the current collection with a new set of items and returns a new instance.

**Usage Scenarios:**

- **Partitioning:** Use `partition` for dividing collections based on conditions, such as separating items by a specific
  property or value.
- **Grouping:** Use `groupBy` to organize collections into categories for analysis, display, or data processing.
- **Splitting:** Use `split` for dividing data evenly, such as paginating results or distributing workloads.
- **Chunking:** Use `chunk` for batching items, especially in scenarios involving iterative processing.

**Benefits:**

- **Flexibility:** Supports both key-based and callback-based grouping and partitioning.
- **Immutability:** Ensures the original collection remains unchanged, returning new instances for each operation.
- **Efficiency:** Provides structured methods for working with large collections, improving readability and
  maintainability.

This trait is ideal for scenarios requiring the organization and partitioning of collections into meaningful structures
for processing, analysis, or display.

---

#### SetOperationsTrait.php

**Overview:**

The **SetOperationsTrait** provides methods for performing common set operations on collections, such as intersection,
union, difference, merging, and symmetric difference. These operations allow for advanced manipulation and comparison of
collections while maintaining immutability.

The trait enforces the implementation of the `getItems()` and `setItems()` methods by the using class to handle the
underlying data.

**Methods:**

##### intersect(self $collection): static

- **Description:** Computes the intersection of the current collection and the provided collection, returning only the
  items that exist in both.
- **Parameters:**
    - `$collection` (`self`): The collection to intersect with.
- **Returns:** `static` - A new collection containing the intersected items.
- **Throws:**
    - `InvalidArgumentException` if the provided collection is empty.
- **Example:**

    ```
    $arrh1 = new Arrhae(['apple', 'banana', 'cherry']);
    $arrh2 = new Arrhae(['banana', 'cherry', 'date']);
    $intersection = $arrh1->intersect($arrh2);
    // $intersection contains ['banana', 'cherry']
    ```

##### union(self $collection): static

- **Description:** Returns the union of the current collection and the provided collection, ensuring uniqueness of
  items.
- **Parameters:**
    - `$collection` (`self`): The collection to union with.
- **Returns:** `static` - A new collection containing all unique items from both collections.
- **Example:**

    ```
    $arrh1 = new Arrhae(['apple', 'banana']);
    $arrh2 = new Arrhae(['banana', 'cherry']);
    $union = $arrh1->union($arrh2);
    // $union contains ['apple', 'banana', 'cherry']
    ```

##### diff(self $collection): static

- **Description:** Computes the difference between the current collection and the provided collection, returning items
  that exist in the current collection but not in the provided one.
- **Parameters:**
    - `$collection` (`self`): The collection to compare against.
- **Returns:** `static` - A new collection containing the difference of items.
- **Example:**

    ```
    $arrh1 = new Arrhae(['apple', 'banana', 'cherry']);
    $arrh2 = new Arrhae(['banana', 'date']);
    $difference = $arrh1->diff($arrh2);
    // $difference contains ['apple', 'cherry']
    ```

##### merge(self $collection): static

- **Description:** Merges the current collection with the provided collection, combining all items without ensuring
  uniqueness.
- **Parameters:**
    - `$collection` (`self`): The collection to merge with.
- **Returns:** `static` - A new collection with merged items.
- **Example:**

    ```
    $arrh1 = new Arrhae(['apple', 'banana']);
    $arrh2 = new Arrhae(['cherry', 'date']);
    $merged = $arrh1->merge($arrh2);
    // $merged contains ['apple', 'banana', 'cherry', 'date']
    ```

##### symmetricDifference(self $collection): static

- **Description:** Computes the symmetric difference between two collections, returning items that are in either
  collection but not in both.
- **Parameters:**
    - `$collection` (`self`): The collection to compare against.
- **Returns:** `static` - A new collection with the symmetric difference of items.
- **Throws:**
    - `InvalidArgumentException` if the provided collection is invalid or contains incompatible types.
- **Example:**

    ```
    $arrh1 = new Arrhae(['apple', 'banana', 'cherry']);
    $arrh2 = new Arrhae(['banana', 'date', 'fig']);
    $symDifference = $arrh1->symmetricDifference($arrh2);
    // $symDifference contains ['apple', 'cherry', 'date', 'fig']
    ```

**Abstract Methods:**

- **getItems(): array**
    - **Description:** Retrieves the current collection of items as an array.

- **setItems(array $items): static**
    - **Description:** Replaces the current collection with a new set of items and returns a new instance.

**Usage Scenarios:**

- **Intersection:** Use `intersect` to find common elements between two datasets.
- **Union:** Use `union` to combine datasets into a single collection without duplicates.
- **Difference:** Use `diff` to identify unique elements in one dataset compared to another.
- **Merging:** Use `merge` to concatenate datasets without checking for duplicates.
- **Symmetric Difference:** Use `symmetricDifference` to find items exclusive to each dataset.

**Benefits:**

- **Flexibility:** Supports common set operations with intuitive methods.
- **Immutability:** Returns new instances for each operation, preserving the original collection.
- **Consistency:** Handles collections of various types consistently using the `toArray()` method.

This trait is ideal for applications requiring mathematical or logical operations on collections, such as managing
overlapping datasets, finding unique items, or combining multiple datasets efficiently.

---

#### SortOperationsTrait.php

**Overview:**

The **SortOperationsTrait** provides robust and flexible sorting functionalities for collections. It allows for sorting
by keys or custom criteria, reversing the order of items, and sorting by multiple criteria. The trait is designed to
work seamlessly in real-world applications where complex data structures require advanced sorting logic.

This trait requires implementing classes to define `getItems()` and `setItems()` methods for accessing and updating the
underlying collection.

**Requirements:**

Classes using this trait must implement:

- **getItems(): array**
    - **Description:** Retrieves the current collection of items.

- **setItems(array $items): static**
    - **Description:** Updates the collection and returns a new instance.

**Provided Methods:**

##### reverse(): static

- **Description:** Reverses the order of the collection.
- **Real-World Example:**

    ```
    $orders = new Arrhae([
        ['id' => 1, 'amount' => 100, 'date' => '2024-01-01'],
        ['id' => 2, 'amount' => 150, 'date' => '2024-01-02'],
        ['id' => 3, 'amount' => 200, 'date' => '2024-01-03'],
    ]);
    $reversedOrders = $orders->reverse();
    // Result:
    // [
    //     ['id' => 3, 'amount' => 200, 'date' => '2024-01-03'],
    //     ['id' => 2, 'amount' => 150, 'date' => '2024-01-02'],
    //     ['id' => 1, 'amount' => 100, 'date' => '2024-01-01'],
    // ]
    ```

##### sortBy(Closure|string $key): static

- **Description:** Sorts items by a specified key or custom comparison function in ascending order.
- **Real-World Example:**

    ```
    $products = new Arrhae([
        ['name' => 'Laptop', 'price' => 1200, 'stock' => 30],
        ['name' => 'Mouse', 'price' => 20, 'stock' => 200],
        ['name' => 'Keyboard', 'price' => 50, 'stock' => 100],
    ]);
    $sortedProducts = $products->sortBy('price');
    // Result:
    // [
    //     ['name' => 'Mouse', 'price' => 20, 'stock' => 200],
    //     ['name' => 'Keyboard', 'price' => 50, 'stock' => 100],
    //     ['name' => 'Laptop', 'price' => 1200, 'stock' => 30],
    // ]
    ```

##### sortDesc(Closure|string $key): static

- **Description:** Sorts items by a specified key or custom comparison function in descending order.
- **Real-World Example:**

    ```
    $users = new Arrhae([
        ['name' => 'Alice', 'lastLogin' => '2024-12-15'],
        ['name' => 'Bob', 'lastLogin' => '2024-12-10'],
        ['name' => 'Charlie', 'lastLogin' => '2024-12-20'],
    ]);
    $sortedUsers = $users->sortDesc('lastLogin');
    // Result:
    // [
    //     ['name' => 'Charlie', 'lastLogin' => '2024-12-20'],
    //     ['name' => 'Alice', 'lastLogin' => '2024-12-15'],
    //     ['name' => 'Bob', 'lastLogin' => '2024-12-10'],
    // ]
    ```

##### sortKeys(): static

- **Description:** Sorts the collection by its keys in ascending order.
- **Real-World Example:**

    ```
    $inventory = new Arrhae([
        'C3' => ['item' => 'Cables', 'quantity' => 50],
        'A1' => ['item' => 'Adapters', 'quantity' => 100],
        'B2' => ['item' => 'Batteries', 'quantity' => 75],
    ]);
    $sortedInventory = $inventory->sortKeys();
    // Result:
    // [
    //     'A1' => ['item' => 'Adapters', 'quantity' => 100],
    //     'B2' => ['item' => 'Batteries', 'quantity' => 75],
    //     'C3' => ['item' => 'Cables', 'quantity' => 50],
    // ]
    ```

##### sortKeysDesc(): static

- **Description:** Sorts the collection by its keys in descending order.
- **Real-World Example:**

    ```
    $logs = new Arrhae([
        'log3' => ['level' => 'error', 'message' => 'System failure'],
        'log1' => ['level' => 'info', 'message' => 'Application started'],
        'log2' => ['level' => 'warning', 'message' => 'High memory usage'],
    ]);
    $sortedLogs = $logs->sortKeysDesc();
    // Result:
    // [
    //     'log3' => ['level' => 'error', 'message' => 'System failure'],
    //     'log2' => ['level' => 'warning', 'message' => 'High memory usage'],
    //     'log1' => ['level' => 'info', 'message' => 'Application started'],
    // ]
    ```

##### sortByMultiple(array $criteria): static

- **Description:** Sorts the collection by multiple criteria with specified orders.
- **Real-World Example:**

    ```
    $employees = new Arrhae([
        ['name' => 'Alice', 'department' => 'HR', 'salary' => 50000],
        ['name' => 'Bob', 'department' => 'IT', 'salary' => 60000],
        ['name' => 'Charlie', 'department' => 'HR', 'salary' => 55000],
        ['name' => 'Dave', 'department' => 'IT', 'salary' => 50000],
    ]);
    $sortedEmployees = $employees->sortByMultiple([
        'department' => 'asc',
        'salary' => 'desc',
    ]);
    // Result:
    // [
    //     ['name' => 'Charlie', 'department' => 'HR', 'salary' => 55000],
    //     ['name' => 'Alice', 'department' => 'HR', 'salary' => 50000],
    //     ['name' => 'Bob', 'department' => 'IT', 'salary' => 60000],
    //     ['name' => 'Dave', 'department' => 'IT', 'salary' => 50000],
    // ]
    ```

**Exception Handling:**

- **InvalidArgumentException:**
    - Thrown when a key does not exist in one or more items for sorting.

**Usage Notes:**

- **Immutability:** All methods return a new instance, preserving the original collection.
- **Flexible Sorting:** Methods support both key-based and custom callback sorting.
- **Multi-Criteria Sorting:** Allows for granular control over sorting logic, making it suitable for real-world business
  cases.

This trait is particularly useful for classes that handle collections requiring sorting and randomization of data,
ensuring flexibility and robustness in data handling operations.

---

#### PartitioningTrait.php

**Overview:**

The **PartitioningTrait** provides methods for dividing, grouping, and chunking collections of data. This trait is
designed for scenarios requiring partitioning based on conditions, grouping items by specific keys or callback logic,
and dividing collections into specified groups or chunks. It ensures flexibility and immutability in data manipulation.

The trait enforces the implementation of the `getItems()` and `setItems()` methods by the using class to handle the
underlying data collection.

**Methods:**

##### partition(Closure $callback): array

- **Description:** Splits the collection into two groups based on a callback. Items satisfying the callback condition
  are placed in one group, and the remaining items in another.
- **Parameters:**
    - `$callback` (`Closure`): A callback function that determines the partition condition.
- **Returns:** `array` - An array containing two new collections:
    - The first collection contains items matching the condition.
    - The second collection contains items not matching the condition.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry', 'date']);
    [$fruitsWithA, $fruitsWithoutA] = $arrh->partition(fn($item) => strpos($item, 'a') !== false);
    // $fruitsWithA contains ['apple', 'banana', 'date']
    // $fruitsWithoutA contains ['cherry']
    ```

##### groupBy(Closure|string $key): static

- **Description:** Groups items in the collection by a specific key or the result of a callback function.
- **Parameters:**
    - `$key` (`Closure|string`): A key for grouping or a callback function that returns the group key for each item.
- **Returns:** `static` - A new collection where each group is a sub-collection.
- **Throws:**
    - `InvalidArgumentException` if a string key is provided and one or more items do not contain the key.
- **Example:**

    ```
    $arrh = new Arrhae([
        ['type' => 'fruit', 'name' => 'apple'],
        ['type' => 'fruit', 'name' => 'banana'],
        ['type' => 'vegetable', 'name' => 'carrot'],
    ]);
    
    // Group by a string key
    $grouped = $arrh->groupBy('type');
    // $grouped contains:
    // [
    //     'fruit' => new Arrhae([
    //         ['type' => 'fruit', 'name' => 'apple'],
    //         ['type' => 'fruit', 'name' => 'banana'],
    //     ]),
    //     'vegetable' => new Arrhae([
    //         ['type' => 'vegetable', 'name' => 'carrot'],
    //     ]),
    // ]
    
    // Group by a callback
    $groupedByLength = $arrh->groupBy(fn($item) => strlen($item['name']));
    // $groupedByLength contains:
    // [
    //     5 => new Arrhae([['type' => 'fruit', 'name' => 'apple']]),
    //     6 => new Arrhae([['type' => 'fruit', 'name' => 'banana'], ['type' => 'vegetable', 'name' => 'carrot']]),
    // ]
    ```

##### split(int $numberOfGroups): static

- **Description:** Divides the collection into a specified number of groups as evenly as possible.
- **Parameters:**
    - `$numberOfGroups` (`int`): The number of groups to create.
- **Returns:** `static` - A collection containing the specified number of groups as sub-collections.
- **Throws:**
    - `InvalidArgumentException` if `$numberOfGroups` is less than `1`.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry', 'date', 'elderberry']);
    $groups = $arrh->split(2);
    // $groups contains:
    // [
    //     new Arrhae(['apple', 'banana', 'cherry']),
    //     new Arrhae(['date', 'elderberry']),
    // ]
    ```

##### chunk(int $size): static

- **Description:** Splits the collection into chunks of a specified size.
- **Parameters:**
    - `$size` (`int`): The number of items in each chunk.
- **Returns:** `static` - A collection containing chunks as sub-collections.
- **Throws:**
    - `InvalidArgumentException` if `$size` is less than `1`.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry', 'date', 'elderberry']);
    $chunks = $arrh->chunk(2);
    // $chunks contains:
    // [
    //     new Arrhae(['apple', 'banana']),
    //     new Arrhae(['cherry', 'date']),
    //     new Arrhae(['elderberry']),
    // ]
    ```

##### all(): array

- **Description:** Returns all items in the collection.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $allItems = $arrh->all();
    // $allItems contains ['apple', 'banana', 'cherry']
    ```

**Abstract Methods:**

- **getItems(): array**
    - **Description:** Retrieves the current collection of items as an array.

- **setItems(array $items): static**
    - **Description:** Replaces the current collection with a new set of items and returns a new instance.

**Usage Scenarios:**

- **Partitioning:** Use `partition` for dividing collections based on conditions, such as separating items by a specific
  property or value.
- **Grouping:** Use `groupBy` to organize collections into categories for analysis, display, or data processing.
- **Splitting:** Use `split` for dividing data evenly, such as paginating results or distributing workloads.
- **Chunking:** Use `chunk` for batching items, especially in scenarios involving iterative processing.

**Benefits:**

- **Flexibility:** Supports both key-based and callback-based grouping and partitioning.
- **Immutability:** Ensures the original collection remains unchanged, returning new instances for each operation.
- **Efficiency:** Provides structured methods for working with large collections, improving readability and
  maintainability.

This trait is ideal for scenarios requiring the organization and partitioning of collections into meaningful structures
for processing, analysis, or display.

---

#### SetOperationsTrait.php

**Overview:**

The **SetOperationsTrait** provides methods for performing common set operations on collections, such as intersection,
union, difference, merging, and symmetric difference. These operations allow for advanced manipulation and comparison of
collections while maintaining immutability.

The trait enforces the implementation of the `getItems()` and `setItems()` methods by the using class to handle the
underlying data.

**Methods:**

##### intersect(self $collection): static

- **Description:** Computes the intersection of the current collection and the provided collection, returning only the
  items that exist in both.
- **Parameters:**
    - `$collection` (`self`): The collection to intersect with.
- **Returns:** `static` - A new collection containing the intersected items.
- **Throws:**
    - `InvalidArgumentException` if the provided collection is empty.
- **Example:**

    ```
    $arrh1 = new Arrhae(['apple', 'banana', 'cherry']);
    $arrh2 = new Arrhae(['banana', 'cherry', 'date']);
    $intersection = $arrh1->intersect($arrh2);
    // $intersection contains ['banana', 'cherry']
    ```

##### union(self $collection): static

- **Description:** Returns the union of the current collection and the provided collection, ensuring uniqueness of
  items.
- **Parameters:**
    - `$collection` (`self`): The collection to union with.
- **Returns:** `static` - A new collection containing all unique items from both collections.
- **Example:**

    ```
    $arrh1 = new Arrhae(['apple', 'banana']);
    $arrh2 = new Arrhae(['banana', 'cherry']);
    $union = $arrh1->union($arrh2);
    // $union contains ['apple', 'banana', 'cherry']
    ```

##### diff(self $collection): static

- **Description:** Computes the difference between the current collection and the provided collection, returning items
  that exist in the current collection but not in the provided one.
- **Parameters:**
    - `$collection` (`self`): The collection to compare against.
- **Returns:** `static` - A new collection containing the difference of items.
- **Example:**

    ```
    $arrh1 = new Arrhae(['apple', 'banana', 'cherry']);
    $arrh2 = new Arrhae(['banana', 'date']);
    $difference = $arrh1->diff($arrh2);
    // $difference contains ['apple', 'cherry']
    ```

##### merge(self $collection): static

- **Description:** Merges the current collection with the provided collection, combining all items without ensuring
  uniqueness.
- **Parameters:**
    - `$collection` (`self`): The collection to merge with.
- **Returns:** `static` - A new collection with merged items.
- **Example:**

    ```
    $arrh1 = new Arrhae(['apple', 'banana']);
    $arrh2 = new Arrhae(['cherry', 'date']);
    $merged = $arrh1->merge($arrh2);
    // $merged contains ['apple', 'banana', 'cherry', 'date']
    ```

##### symmetricDifference(self $collection): static

- **Description:** Computes the symmetric difference between two collections, returning items that are in either
  collection but not in both.
- **Parameters:**
    - `$collection` (`self`): The collection to compare against.
- **Returns:** `static` - A new collection with the symmetric difference of items.
- **Throws:**
    - `InvalidArgumentException` if the provided collection is invalid or contains incompatible types.
- **Example:**

    ```
    $arrh1 = new Arrhae(['apple', 'banana', 'cherry']);
    $arrh2 = new Arrhae(['banana', 'date', 'fig']);
    $symDifference = $arrh1->symmetricDifference($arrh2);
    // $symDifference contains ['apple', 'cherry', 'date', 'fig']
    ```

**Abstract Methods:**

- **getItems(): array**
    - **Description:** Retrieves the current collection of items as an array.

- **setItems(array $items): static**
    - **Description:** Replaces the current collection with a new set of items and returns a new instance.

**Usage Scenarios:**

- **Intersection:** Use `intersect` to find common elements between two datasets.
- **Union:** Use `union` to combine datasets into a single collection without duplicates.
- **Difference:** Use `diff` to identify unique elements in one dataset compared to another.
- **Merging:** Use `merge` to concatenate datasets without checking for duplicates.
- **Symmetric Difference:** Use `symmetricDifference` to find items exclusive to each dataset.

**Benefits:**

- **Flexibility:** Supports common set operations with intuitive methods.
- **Immutability:** Returns new instances for each operation, preserving the original collection.
- **Consistency:** Handles collections of various types consistently using the `toArray()` method.

This trait is ideal for applications requiring mathematical or logical operations on collections, such as managing
overlapping datasets, finding unique items, or combining multiple datasets efficiently.

---

#### SortOperationsTrait.php

**Overview:**

The **SortOperationsTrait** provides robust and flexible sorting functionalities for collections. It allows for sorting
by keys or custom criteria, reversing the order of items, and sorting by multiple criteria. The trait is designed to
work seamlessly in real-world applications where complex data structures require advanced sorting logic.

This trait requires implementing classes to define `getItems()` and `setItems()` methods for accessing and updating the
underlying collection.

**Requirements:**

Classes using this trait must implement:

- **getItems(): array**
    - **Description:** Retrieves the current collection of items.

- **setItems(array $items): static**
    - **Description:** Updates the collection and returns a new instance.

**Provided Methods:**

##### reverse(): static

- **Description:** Reverses the order of the collection.
- **Real-World Example:**

    ```
    $orders = new Arrhae([
        ['id' => 1, 'amount' => 100, 'date' => '2024-01-01'],
        ['id' => 2, 'amount' => 150, 'date' => '2024-01-02'],
        ['id' => 3, 'amount' => 200, 'date' => '2024-01-03'],
    ]);
    $reversedOrders = $orders->reverse();
    // Result:
    // [
    //     ['id' => 3, 'amount' => 200, 'date' => '2024-01-03'],
    //     ['id' => 2, 'amount' => 150, 'date' => '2024-01-02'],
    //     ['id' => 1, 'amount' => 100, 'date' => '2024-01-01'],
    // ]
    ```

##### sortBy(Closure|string $key): static

- **Description:** Sorts items by a specified key or custom comparison function in ascending order.
- **Real-World Example:**

    ```
    $products = new Arrhae([
        ['name' => 'Laptop', 'price' => 1200, 'stock' => 30],
        ['name' => 'Mouse', 'price' => 20, 'stock' => 200],
        ['name' => 'Keyboard', 'price' => 50, 'stock' => 100],
    ]);
    $sortedProducts = $products->sortBy('price');
    // Result:
    // [
    //     ['name' => 'Mouse', 'price' => 20, 'stock' => 200],
    //     ['name' => 'Keyboard', 'price' => 50, 'stock' => 100],
    //     ['name' => 'Laptop', 'price' => 1200, 'stock' => 30],
    // ]
    ```

##### sortDesc(Closure|string $key): static

- **Description:** Sorts items by a specified key or custom comparison function in descending order.
- **Real-World Example:**

    ```
    $users = new Arrhae([
        ['name' => 'Alice', 'lastLogin' => '2024-12-15'],
        ['name' => 'Bob', 'lastLogin' => '2024-12-10'],
        ['name' => 'Charlie', 'lastLogin' => '2024-12-20'],
    ]);
    $sortedUsers = $users->sortDesc('lastLogin');
    // Result:
    // [
    //     ['name' => 'Charlie', 'lastLogin' => '2024-12-20'],
    //     ['name' => 'Alice', 'lastLogin' => '2024-12-15'],
    //     ['name' => 'Bob', 'lastLogin' => '2024-12-10'],
    // ]
    ```

##### sortKeys(): static

- **Description:** Sorts the collection by its keys in ascending order.
- **Real-World Example:**

    ```
    $inventory = new Arrhae([
        'C3' => ['item' => 'Cables', 'quantity' => 50],
        'A1' => ['item' => 'Adapters', 'quantity' => 100],
        'B2' => ['item' => 'Batteries', 'quantity' => 75],
    ]);
    $sortedInventory = $inventory->sortKeys();
    // Result:
    // [
    //     'A1' => ['item' => 'Adapters', 'quantity' => 100],
    //     'B2' => ['item' => 'Batteries', 'quantity' => 75],
    //     'C3' => ['item' => 'Cables', 'quantity' => 50],
    // ]
    ```

##### sortKeysDesc(): static

- **Description:** Sorts the collection by its keys in descending order.
- **Real-World Example:**

    ```
    $logs = new Arrhae([
        'log3' => ['level' => 'error', 'message' => 'System failure'],
        'log1' => ['level' => 'info', 'message' => 'Application started'],
        'log2' => ['level' => 'warning', 'message' => 'High memory usage'],
    ]);
    $sortedLogs = $logs->sortKeysDesc();
    // Result:
    // [
    //     'log3' => ['level' => 'error', 'message' => 'System failure'],
    //     'log2' => ['level' => 'warning', 'message' => 'High memory usage'],
    //     'log1' => ['level' => 'info', 'message' => 'Application started'],
    // ]
    ```

##### sortByMultiple(array $criteria): static

- **Description:** Sorts the collection by multiple criteria with specified orders.
- **Real-World Example:**

    ```
    $employees = new Arrhae([
        ['name' => 'Alice', 'department' => 'HR', 'salary' => 50000],
        ['name' => 'Bob', 'department' => 'IT', 'salary' => 60000],
        ['name' => 'Charlie', 'department' => 'HR', 'salary' => 55000],
        ['name' => 'Dave', 'department' => 'IT', 'salary' => 50000],
    ]);
    $sortedEmployees = $employees->sortByMultiple([
        'department' => 'asc',
        'salary' => 'desc',
    ]);
    // Result:
    // [
    //     ['name' => 'Charlie', 'department' => 'HR', 'salary' => 55000],
    //     ['name' => 'Alice', 'department' => 'HR', 'salary' => 50000],
    //     ['name' => 'Bob', 'department' => 'IT', 'salary' => 60000],
    //     ['name' => 'Dave', 'department' => 'IT', 'salary' => 50000],
    // ]
    ```

**Exception Handling:**

- **InvalidArgumentException:**
    - Thrown when a key does not exist in one or more items for sorting.

**Usage Notes:**

- **Immutability:** All methods return a new instance, preserving the original collection.
- **Flexible Sorting:** Methods support both key-based and custom callback sorting.
- **Multi-Criteria Sorting:** Allows for granular control over sorting logic, making it suitable for real-world business
  cases.

This trait is particularly useful for classes that handle collections requiring sorting and randomization of data,
ensuring flexibility and robustness in data handling operations.

---

#### PartitioningTrait.php

**Overview:**

The **PartitioningTrait** provides methods for dividing, grouping, and chunking collections of data. This trait is
designed for scenarios requiring partitioning based on conditions, grouping items by specific keys or callback logic,
and dividing collections into specified groups or chunks. It ensures flexibility and immutability in data manipulation.

The trait enforces the implementation of the `getItems()` and `setItems()` methods by the using class to handle the
underlying data collection.

**Methods:**

##### partition(Closure $callback): array

- **Description:** Splits the collection into two groups based on a callback. Items satisfying the callback condition
  are placed in one group, and the remaining items in another.
- **Parameters:**
    - `$callback` (`Closure`): A callback function that determines the partition condition.
- **Returns:** `array` - An array containing two new collections:
    - The first collection contains items matching the condition.
    - The second collection contains items not matching the condition.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry', 'date']);
    [$fruitsWithA, $fruitsWithoutA] = $arrh->partition(fn($item) => strpos($item, 'a') !== false);
    // $fruitsWithA contains ['apple', 'banana', 'date']
    // $fruitsWithoutA contains ['cherry']
    ```

##### groupBy(Closure|string $key): static

- **Description:** Groups items in the collection by a specific key or the result of a callback function.
- **Parameters:**
    - `$key` (`Closure|string`): A key for grouping or a callback function that returns the group key for each item.
- **Returns:** `static` - A new collection where each group is a sub-collection.
- **Throws:**
    - `InvalidArgumentException` if a string key is provided and one or more items do not contain the key.
- **Example:**

    ```
    $arrh = new Arrhae([
        ['type' => 'fruit', 'name' => 'apple'],
        ['type' => 'fruit', 'name' => 'banana'],
        ['type' => 'vegetable', 'name' => 'carrot'],
    ]);
    
    // Group by a string key
    $grouped = $arrh->groupBy('type');
    // $grouped contains:
    // [
    //     'fruit' => new Arrhae([
    //         ['type' => 'fruit', 'name' => 'apple'],
    //         ['type' => 'fruit', 'name' => 'banana'],
    //     ]),
    //     'vegetable' => new Arrhae([
    //         ['type' => 'vegetable', 'name' => 'carrot'],
    //     ]),
    // ]
    
    // Group by a callback
    $groupedByLength = $arrh->groupBy(fn($item) => strlen($item['name']));
    // $groupedByLength contains:
    // [
    //     5 => new Arrhae([['type' => 'fruit', 'name' => 'apple']]),
    //     6 => new Arrhae([['type' => 'fruit', 'name' => 'banana'], ['type' => 'vegetable', 'name' => 'carrot']]),
    // ]
    ```

##### split(int $numberOfGroups): static

- **Description:** Divides the collection into a specified number of groups as evenly as possible.
- **Parameters:**
    - `$numberOfGroups` (`int`): The number of groups to create.
- **Returns:** `static` - A collection containing the specified number of groups as sub-collections.
- **Throws:**
    - `InvalidArgumentException` if `$numberOfGroups` is less than `1`.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry', 'date', 'elderberry']);
    $groups = $arrh->split(2);
    // $groups contains:
    // [
    //     new Arrhae(['apple', 'banana', 'cherry']),
    //     new Arrhae(['date', 'elderberry']),
    // ]
    ```

##### chunk(int $size): static

- **Description:** Splits the collection into chunks of a specified size.
- **Parameters:**
    - `$size` (`int`): The number of items in each chunk.
- **Returns:** `static` - A collection containing chunks as sub-collections.
- **Throws:**
    - `InvalidArgumentException` if `$size` is less than `1`.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry', 'date', 'elderberry']);
    $chunks = $arrh->chunk(2);
    // $chunks contains:
    // [
    //     new Arrhae(['apple', 'banana']),
    //     new Arrhae(['cherry', 'date']),
    //     new Arrhae(['elderberry']),
    // ]
    ```

##### all(): array

- **Description:** Returns all items in the collection.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $allItems = $arrh->all();
    // $allItems contains ['apple', 'banana', 'cherry']
    ```

**Abstract Methods:**

- **getItems(): array**
    - **Description:** Retrieves the current collection of items as an array.

- **setItems(array $items): static**
    - **Description:** Replaces the current collection with a new set of items and returns a new instance.

**Usage Scenarios:**

- **Partitioning:** Use `partition` for dividing collections based on conditions, such as separating items by a specific
  property or value.
- **Grouping:** Use `groupBy` to organize collections into categories for analysis, display, or data processing.
- **Splitting:** Use `split` for dividing data evenly, such as paginating results or distributing workloads.
- **Chunking:** Use `chunk` for batching items, especially in scenarios involving iterative processing.

**Benefits:**

- **Flexibility:** Supports both key-based and callback-based grouping and partitioning.
- **Immutability:** Ensures the original collection remains unchanged, returning new instances for each operation.
- **Efficiency:** Provides structured methods for working with large collections, improving readability and
  maintainability.

This trait is ideal for scenarios requiring the organization and partitioning of collections into meaningful structures
for processing, analysis, or display.

---

#### SortOperationsTrait.php

**Overview:**

The **SortOperationsTrait** provides robust and flexible sorting functionalities for collections. It allows for sorting
by keys or custom criteria, reversing the order of items, and sorting by multiple criteria. The trait is designed to
work seamlessly in real-world applications where complex data structures require advanced sorting logic.

This trait requires implementing classes to define `getItems()` and `setItems()` methods for accessing and updating the
underlying collection.

**Requirements:**

Classes using this trait must implement:

- **getItems(): array**
    - **Description:** Retrieves the current collection of items.

- **setItems(array $items): static**
    - **Description:** Updates the collection and returns a new instance.

**Provided Methods:**

##### reverse(): static

- **Description:** Reverses the order of the collection.
- **Real-World Example:**

    ```
    $orders = new Arrhae([
        ['id' => 1, 'amount' => 100, 'date' => '2024-01-01'],
        ['id' => 2, 'amount' => 150, 'date' => '2024-01-02'],
        ['id' => 3, 'amount' => 200, 'date' => '2024-01-03'],
    ]);
    $reversedOrders = $orders->reverse();
    // Result:
    // [
    //     ['id' => 3, 'amount' => 200, 'date' => '2024-01-03'],
    //     ['id' => 2, 'amount' => 150, 'date' => '2024-01-02'],
    //     ['id' => 1, 'amount' => 100, 'date' => '2024-01-01'],
    // ]
    ```

##### sortBy(Closure|string $key): static

- **Description:** Sorts items by a specified key or custom comparison function in ascending order.
- **Real-World Example:**

    ```
    $products = new Arrhae([
        ['name' => 'Laptop', 'price' => 1200, 'stock' => 30],
        ['name' => 'Mouse', 'price' => 20, 'stock' => 200],
        ['name' => 'Keyboard', 'price' => 50, 'stock' => 100],
    ]);
    $sortedProducts = $products->sortBy('price');
    // Result:
    // [
    //     ['name' => 'Mouse', 'price' => 20, 'stock' => 200],
    //     ['name' => 'Keyboard', 'price' => 50, 'stock' => 100],
    //     ['name' => 'Laptop', 'price' => 1200, 'stock' => 30],
    // ]
    ```

##### sortDesc(Closure|string $key): static

- **Description:** Sorts items by a specified key or custom comparison function in descending order.
- **Real-World Example:**

    ```
    $users = new Arrhae([
        ['name' => 'Alice', 'lastLogin' => '2024-12-15'],
        ['name' => 'Bob', 'lastLogin' => '2024-12-10'],
        ['name' => 'Charlie', 'lastLogin' => '2024-12-20'],
    ]);
    $sortedUsers = $users->sortDesc('lastLogin');
    // Result:
    // [
    //     ['name' => 'Charlie', 'lastLogin' => '2024-12-20'],
    //     ['name' => 'Alice', 'lastLogin' => '2024-12-15'],
    //     ['name' => 'Bob', 'lastLogin' => '2024-12-10'],
    // ]
    ```

##### sortKeys(): static

- **Description:** Sorts the collection by its keys in ascending order.
- **Real-World Example:**

    ```
    $inventory = new Arrhae([
        'C3' => ['item' => 'Cables', 'quantity' => 50],
        'A1' => ['item' => 'Adapters', 'quantity' => 100],
        'B2' => ['item' => 'Batteries', 'quantity' => 75],
    ]);
    $sortedInventory = $inventory->sortKeys();
    // Result:
    // [
    //     'A1' => ['item' => 'Adapters', 'quantity' => 100],
    //     'B2' => ['item' => 'Batteries', 'quantity' => 75],
    //     'C3' => ['item' => 'Cables', 'quantity' => 50],
    // ]
    ```

##### sortKeysDesc(): static

- **Description:** Sorts the collection by its keys in descending order.
- **Real-World Example:**

    ```
    $logs = new Arrhae([
        'log3' => ['level' => 'error', 'message' => 'System failure'],
        'log1' => ['level' => 'info', 'message' => 'Application started'],
        'log2' => ['level' => 'warning', 'message' => 'High memory usage'],
    ]);
    $sortedLogs = $logs->sortKeysDesc();
    // Result:
    // [
    //     'log3' => ['level' => 'error', 'message' => 'System failure'],
    //     'log2' => ['level' => 'warning', 'message' => 'High memory usage'],
    //     'log1' => ['level' => 'info', 'message' => 'Application started'],
    // ]
    ```

##### sortByMultiple(array $criteria): static

- **Description:** Sorts the collection by multiple criteria with specified orders.
- **Real-World Example:**

    ```
    $employees = new Arrhae([
        ['name' => 'Alice', 'department' => 'HR', 'salary' => 50000],
        ['name' => 'Bob', 'department' => 'IT', 'salary' => 60000],
        ['name' => 'Charlie', 'department' => 'HR', 'salary' => 55000],
        ['name' => 'Dave', 'department' => 'IT', 'salary' => 50000],
    ]);
    $sortedEmployees = $employees->sortByMultiple([
        'department' => 'asc',
        'salary' => 'desc',
    ]);
    // Result:
    // [
    //     ['name' => 'Charlie', 'department' => 'HR', 'salary' => 55000],
    //     ['name' => 'Alice', 'department' => 'HR', 'salary' => 50000],
    //     ['name' => 'Bob', 'department' => 'IT', 'salary' => 60000],
    //     ['name' => 'Dave', 'department' => 'IT', 'salary' => 50000],
    // ]
    ```

**Exception Handling:**

- **InvalidArgumentException:**
    - Thrown when a key does not exist in one or more items for sorting.

**Usage Notes:**

- **Immutability:** All methods return a new instance, preserving the original collection.
- **Flexible Sorting:** Methods support both key-based and custom callback sorting.
- **Multi-Criteria Sorting:** Allows for granular control over sorting logic, making it suitable for real-world business
  cases.

This trait is particularly useful for classes that handle collections requiring sorting and randomization of data,
ensuring flexibility and robustness in data handling operations.

---

#### StructureConversionTrait.php

**Purpose:**

The **StructureConversionTrait** provides methods for transforming the structure of collections. It enables the
flattening of multidimensional arrays into dot-notated arrays, converting collections into indexed lists, and
reconstructing dot-notated arrays back into multidimensional structures.

**Key Responsibilities:**

- Transform collections between multidimensional and flat structures.
- Facilitate easy data export and import in various formats.
- Support complex data manipulation tasks requiring structural changes.

**Methods:**

##### dot(): static

- **Description:** Flattens a multidimensional array into a dot-notated structure where keys represent the nested
  hierarchy.
- **Returns:** `static` - A new instance containing the dot-notated keys and their corresponding values.
- **Throws:**
    - `InvalidArgumentException` if the collection contains non-array items or if keys are not scalar or `null`.
- **Example:**

    ```
    $arrh = new Arrhae([
        'user' => [
            'name' => 'John Doe',
            'address' => [
                'street' => '123 Main St',
                'city' => 'Anytown'
            ]
        ],
        'status' => 'active'
    ]);
    $flattened = $arrh->dot();
    // $flattened contains:
    // [
    //     'user.name' => 'John Doe',
    //     'user.address.street' => '123 Main St',
    //     'user.address.city' => 'Anytown',
    //     'status' => 'active'
    // ]
    ```

##### toList(): static

- **Description:** Converts the collection into an indexed list by discarding keys and retaining only values.
- **Returns:** `static` - A new instance containing an indexed list of values.
- **Example:**

    ```
    $arrh = new Arrhae(['first' => 'apple', 'second' => 'banana', 'third' => 'cherry']);
    $list = $arrh->toList();
    // $list contains ['apple', 'banana', 'cherry']
    ```

##### unDot(): static

- **Description:** Reconstructs a dot-notated array into its original multidimensional structure.
- **Returns:** `static` - A new instance containing the restored multidimensional array.
- **Example:**

    ```
    $flattened = new Arrhae([
        'user.name' => 'John Doe',
        'user.address.street' => '123 Main St',
        'user.address.city' => 'Anytown',
        'status' => 'active'
    ]);
    $original = $flattened->unDot();
    // $original contains:
    // [
    //     'user' => [
    //         'name' => 'John Doe',
    //         'address' => [
    //             'street' => '123 Main St',
    //             'city' => 'Anytown'
    //         ]
    //     ],
    //     'status' => 'active'
    // ]
    ```

**Abstract Methods:**

- **getItems(): array**
    - **Description:** Retrieves the current collection of items as an array.

- **setItems(array $items): static**
    - **Description:** Replaces the current collection with a new set of items and returns a new instance.

**Usage Scenarios:**

- **Dot-Notated Transformation:** Use `dot()` to flatten nested data structures for easier manipulation or storage in
  flat formats like databases or logs.
- **Indexed Lists:** Use `toList()` to create sequential arrays for processing or display.
- **Restoring Structure:** Use `unDot()` to reverse the flattening process and restore hierarchical data formats.

**Benefits:**

- **Versatility:** Supports transformations between multidimensional and flat structures, catering to various data
  storage and manipulation needs.
- **Immutability:** Ensures the original collection remains unchanged by returning new instances for each
  transformation.
- **Ease of Use:** Simplifies handling of deeply nested or flat data formats.

This trait is ideal for applications involving complex data transformations, such as configurations, JSON processing, or
hierarchical data manipulation.

---

#### TransformationTrait.php

**Purpose:**

The **TransformationTrait** provides methods for transforming the structure and content of collections. It includes
capabilities for flattening multidimensional arrays, applying callbacks for mapping or modifying items, and creating
customized mappings with new keys.

**Key Responsibilities:**

- Facilitate complex data transformations within collections.
- Enable customizable mapping and modification of collection items.
- Support both simple and advanced transformation use cases.

**Methods:**

##### flatten(): static

- **Description:** Flattens a multidimensional array into a single-dimensional array. Keys from the original structure
  are disregarded.
- **Returns:** `static` - A new instance containing the flattened array.
- **Throws:**
    - `InvalidArgumentException` if the collection is not an array.
- **Example:**

    ```
    $arrh = new Arrhae([
        'fruits' => ['apple', 'banana'],
        'vegetables' => ['carrot', 'lettuce'],
        'dairy' => 'milk'
    ]);
    $flattened = $arrh->flatten();
    // $flattened contains ['apple', 'banana', 'carrot', 'lettuce', 'milk']
    ```

##### flatMap(Closure $callback): static

- **Description:** Applies a callback to each item in the collection, where the callback should return an array. The
  resulting arrays are then merged into a single, flattened array.
- **Returns:** `static` - A new instance containing the mapped and flattened array.
- **Throws:**
    - `InvalidArgumentException` if the callback does not return an array.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $flatMapped = $arrh->flatMap(function($item) {
        return [$item, strtoupper($item)];
    });
    // $flatMapped contains ['apple', 'APPLE', 'banana', 'BANANA', 'cherry', 'CHERRY']
    ```

##### mapWithKeys(Closure $callback): static

- **Description:** Maps items in the collection to a new key-value pair using a callback. The callback should return an
  associative array with exactly one key-value pair.
- **Returns:** `static` - A new instance containing the mapped keys and values.
- **Throws:**
    - `InvalidArgumentException` if the callback does not return an associative array with a single key-value pair.
    - `InvalidArgumentException` if the callback returns duplicate keys.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'cherry']);
    $mappedWithKeys = $arrh->mapWithKeys(function($item) {
        return [$item => strlen($item)];
    });
    // $mappedWithKeys contains ['apple' => 5, 'banana' => 6, 'cherry' => 6]
    ```

##### transform(Closure $callback): static

- **Description:** Transforms the collection by applying a callback to each item. The result is a new instance with
  transformed items.
- **Returns:** `static` - A new instance containing the transformed items.
- **Example:**

    ```
    $arrh = new Arrhae([1, 2, 3]);
    $transformed = $arrh->transform(function($item) {
        return $item * 2;
    });
    // $transformed contains [2, 4, 6]
    ```

##### advancedTransform(Closure $callback): static

- **Description:** Applies a callback to each element during iteration, allowing for complex transformations of nested
  structures.
- **Returns:** `static` - A new instance with the transformed collection.
- **Throws:**
    - `InvalidArgumentException` if the callback does not return a valid value.
- **Example:**

    ```
    $arrh = new Arrhae([
        'user' => [
            'name' => 'John Doe',
            'age' => 30
        ],
        'status' => 'active'
    ]);
    $advancedTransformed = $arrh->advancedTransform(function($value, $key) {
        if ($key === 'age') {
            return $value + 1; // Increment age by 1
        }
        return $value;
    });
    // $advancedTransformed contains:
    // [
    //     'user' => [
    //         'name' => 'John Doe',
    //         'age' => 31
    //     ],
    //     'status' => 'active'
    // ]
    ```

**Abstract Methods:**

- **getItems(): array**
    - **Description:** Retrieves the current collection of items.

- **setItems(array $items): static**
    - **Description:** Replaces the current collection with a new set of items and returns a new instance.

- **toArray(): array**
    - **Description:** Converts the collection into an array representation.

**Usage Scenarios:**

- **Flattening Arrays:** Use `flatten()` to reduce nested arrays into a single layer for simpler processing.
- **Custom Mappings:** Use `flatMap()` or `mapWithKeys()` to create custom transformations of items or their keys.
- **Nested Data Transformation:** Use `advancedTransform()` for applying transformations to deeply nested structures.

**Benefits:**

- **Versatility:** Offers tools for both simple and complex transformations.
- **Immutability:** Ensures the original collection remains unchanged by returning new instances.
- **Customizability:** Enables highly flexible data processing with callback functions.

This trait is ideal for use in applications involving data transformations, such as API data normalization, report
generation, or dynamic data visualization.

---

#### AdvancedStringSearchTrait.php

**Overview:**

The **AdvancedStringSearchTrait** enhances the **Arrhae** class with sophisticated string search capabilities. It
leverages fuzzy matching algorithms to enable flexible and intelligent searching within collections. This trait provides
methods for various types of string matching, including fuzzy matches, similarity-based searches, Levenshtein distance
searches, partial matches, token-based matches, phonetic matches, regular expression searches, and custom callback-based
matches. Additionally, it offers functionality to sort matched items based on their similarity to the query,
facilitating ranked search results.

**Purpose:**

The **AdvancedStringSearchTrait** is designed to provide advanced string searching functionalities for collections. By
incorporating fuzzy matching and other intelligent search techniques, it allows developers to perform more flexible and
user-friendly searches within large and complex data sets. This trait is essential for applications that require robust
search capabilities, such as search engines, data filtering tools, and user-driven query interfaces.

**Key Responsibilities:**

- **Fuzzy Matching:** Enable searches that account for minor typos and variations in strings.
- **Similarity-Based Searches:** Find items based on their similarity percentage to a query.
- **Levenshtein Distance Searches:** Locate items within a specific edit distance from the query.
- **Partial Matching:** Identify items containing the query as a substring.
- **Token-Based Matching:** Perform matches based on sorted or unique tokens within strings.
- **Phonetic Matching:** Match items based on their phonetic representation.
- **Regular Expression Searches:** Allow complex pattern-based searches using regex.
- **Custom Callback Matches:** Provide flexibility for custom search criteria through user-defined callbacks.
- **Sorting by Similarity:** Rank search results based on how closely they match the query.

**Methods:**

##### fuzzyMatch(string $query, float $threshold = 70.0, ?string $key = null): static

- **Description:** Performs a fuzzy match on the collection items based on a given query. This method calculates the
  similarity ratio between the query and each item using FuzzyWuzzy, returning items that meet or exceed the specified
  similarity threshold.
- **Parameters:**
    - `string $query`: The search query string.
    - `float $threshold`: The minimum similarity percentage (0 to 100) required for a match. Defaults to `70.0`.
    - `?string $key`: The key to search within if items are associative arrays. Defaults to `null`.
- **Returns:** `static` - A new instance containing the matched items.
- **Throws:**
    - `InvalidArgumentException`: If the threshold is not between `0` and `100`.
- **Example:**

    ```
    use Gemini\DataHandling\ArrayHandling\Arrhae;
    
    $arrh = new Arrhae(['apple', 'banana', 'apricot', 'grape']);
    $results = $arrh->fuzzyMatch('aple', 80);
    // Returns ['apple']
    
    $arrhAssoc = new Arrhae([
        ['name' => 'Alice'],
        ['name' => 'Alicia'],
        ['name' => 'Alina'],
        ['name' => 'Bob'],
    ]);
    $resultsAssoc = $arrhAssoc->fuzzyMatch('Alic', 70, 'name');
    // Returns [
    //     ['name' => 'Alice'],
    //     ['name' => 'Alicia'],
    //     ['name' => 'Alina'],
    // ]
    ```

##### similaritySearch(string $query, float $threshold = 70.0, ?string $key = null): static

- **Description:** Searches for items with a similarity percentage above a specified threshold. This method uses the
  similarity percentage between the query and each item to determine matches.
- **Parameters:**
    - `string $query`: The search query string.
    - `float $threshold`: The minimum similarity percentage (0 to 100). Defaults to `70.0`.
    - `?string $key`: The key to search within if items are associative arrays. Defaults to `null`.
- **Returns:** `static` - A new instance containing the matched items.
- **Throws:**
    - `InvalidArgumentException`: If the threshold is not between `0` and `100`.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'aple', 'apply', 'banana']);
    $results = $arrh->similaritySearch('apple', 80);
    // Returns ['apple', 'aple', 'apply']
    
    $arrhAssoc = new Arrhae([
        ['title' => 'Introduction to PHP'],
        ['title' => 'Advanced PHP Techniques'],
        ['title' => 'PHP for Beginners'],
        ['title' => 'JavaScript Essentials'],
    ]);
    $resultsAssoc = $arrhAssoc->similaritySearch('PHP Intro', 70, 'title');
    // Returns [
    //     ['title' => 'Introduction to PHP'],
    //     ['title' => 'PHP for Beginners'],
    // ]
    ```

##### levenshteinSearch(string $query, int $maxDistance = 2, ?string $key = null): static

- **Description:** Performs a Levenshtein-based search and sorts the results by similarity. This method finds items
  within a certain distance from the query and sorts them by their similarity to the query.
- **Parameters:**
    - `string $query`: The search query string.
    - `int $maxDistance`: The maximum Levenshtein distance allowed. Defaults to `2`.
    - `?string $key`: The key to search within if items are associative arrays. Defaults to `null`.
- **Returns:** `static` - A new instance containing the matched items sorted by similarity.
- **Throws:**
    - `InvalidArgumentException`: If the max distance is negative.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'apricot', 'banana', 'grape', 'pineapple']);
    $results = $arrh->levenshteinSearch('appel', 2);
    // Returns ['apple']
    
    $arrhAssoc = new Arrhae([
        ['name' => 'Alice'],
        ['name' => 'Alicia'],
        ['name' => 'Alina'],
        ['name' => 'Bob'],
    ]);
    $resultsAssoc = $arrhAssoc->levenshteinSearch('Alic', 2, 'name');
    // Returns [
    //     ['name' => 'Alice'],
    //     ['name' => 'Alicia'],
    // ]
    ```

##### partialMatch(string $query, ?string $key = null): static

- **Description:** Performs a partial match on the collection items based on a given query. This method checks if the
  query string is a substring of the target string.
- **Parameters:**
    - `string $query`: The search query string.
    - `?string $key`: The key to search within if items are associative arrays. Defaults to `null`.
- **Returns:** `static` - A new instance containing the matched items.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'apricot', 'grape']);
    $results = $arrh->partialMatch('app');
    // Returns ['apple', 'apricot']
    
    $arrhAssoc = new Arrhae([
        ['name' => 'Alice'],
        ['name' => 'Alicia'],
        ['name' => 'Alina'],
        ['name' => 'Bob'],
    ]);
    $resultsAssoc = $arrhAssoc->partialMatch('Ali', 'name');
    // Returns [
    //     ['name' => 'Alice'],
    //     ['name' => 'Alicia'],
    // ]
    ```

##### tokenSortMatch(string $query, float $threshold = 70.0, ?string $key = null): static

- **Description:** Performs a token sort match on the collection items based on a given query. This method sorts the
  tokens in both the query and target strings and then calculates the similarity ratio.
- **Parameters:**
    - `string $query`: The search query string.
    - `float $threshold`: The minimum similarity percentage (0 to 100) required for a match. Defaults to `70.0`.
    - `?string $key`: The key to search within if items are associative arrays. Defaults to `null`.
- **Returns:** `static` - A new instance containing the matched items.
- **Throws:**
    - `InvalidArgumentException`: If the threshold is not between `0` and `100`.
- **Example:**

    ```
    $arrh = new Arrhae(['apple banana', 'banana apple', 'apple grape', 'banana grape']);
    $results = $arrh->tokenSortMatch('banana apple', 90);
    // Returns ['apple banana', 'banana apple']
    ```

##### tokenSetMatch(string $query, float $threshold = 70.0, ?string $key = null): static

- **Description:** Performs a token set match on the collection items based on a given query. This method calculates the
  similarity ratio between the unique tokens of the query and target strings.
- **Parameters:**
    - `string $query`: The search query string.
    - `float $threshold`: The minimum similarity percentage (0 to 100) required for a match. Defaults to `70.0`.
    - `?string $key`: The key to search within if items are associative arrays. Defaults to `null`.
- **Returns:** `static` - A new instance containing the matched items.
- **Throws:**
    - `InvalidArgumentException`: If the threshold is not between `0` and `100`.
- **Example:**

    ```
    $arrh = new Arrhae(['apple banana', 'banana apple', 'apple grape', 'banana grape']);
    $results = $arrh->tokenSetMatch('apple banana', 90);
    // Returns ['apple banana', 'banana apple']
    ```

##### phoneticMatch(string $query, ?string $key = null): static

- **Description:** Performs a phonetic match on the collection items based on a given query. This method uses the
  Metaphone algorithm to find phonetically similar strings.
- **Parameters:**
    - `string $query`: The search query string.
    - `?string $key`: The key to search within if items are associative arrays. Defaults to `null`.
- **Returns:** `static` - A new instance containing the matched items.
- **Example:**

    ```
    $arrh = new Arrhae(['Smith', 'Smyth', 'Smithe', 'Johnson']);
    $results = $arrh->phoneticMatch('Smyth');
    // Returns ['Smith', 'Smyth', 'Smithe']
    ```

##### regexSearch(string $pattern, ?string $key = null): static

- **Description:** Performs a regular expression search on the collection items based on a given pattern. This method
  returns items that match the provided regular expression pattern.
- **Parameters:**
    - `string $pattern`: The regular expression pattern.
    - `?string $key`: The key to search within if items are associative arrays. Defaults to `null`.
- **Returns:** `static` - A new instance containing the matched items.
- **Throws:**
    - `InvalidArgumentException`: If the provided pattern is invalid.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'apricot', 'grape']);
    $results = $arrh->regexSearch('/^ap/');
    // Returns ['apple', 'apricot']
    
    $arrhAssoc = new Arrhae([
        ['name' => 'Alice'],
        ['name' => 'Alicia'],
        ['name' => 'Alina'],
        ['name' => 'Bob'],
    ]);
    $resultsAssoc = $arrhAssoc->regexSearch('/^Ali/', 'name');
    // Returns [
    //     ['name' => 'Alice'],
    //     ['name' => 'Alicia'],
    // ]
    ```

##### customMatch(callable $callback, ?string $key = null): static

- **Description:** Performs a custom match on the collection items using a user-defined callback. This method allows for
  highly flexible and customizable search criteria.
- **Parameters:**
    - `callable $callback`: The callback function to determine a match. Should return a boolean.
    - `?string $key`: The key to search within if items are associative arrays. Defaults to `null`.
- **Returns:** `static` - A new instance containing the matched items.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'banana', 'apricot', 'grape']);
    $results = $arrh->customMatch(function($item) {
        return strpos($item, 'ap') === 0;
    });
    // Returns ['apple', 'apricot']
    
    $arrhAssoc = new Arrhae([
        ['name' => 'Alice', 'age' => 30],
        ['name' => 'Alicia', 'age' => 25],
        ['name' => 'Alina', 'age' => 28],
        ['name' => 'Bob', 'age' => 35],
    ]);
    $resultsAssoc = $arrhAssoc->customMatch(function($value, $item) {
        return $item['age'] > 27;
    }, 'age');
    // Returns [
    //     ['name' => 'Alice', 'age' => 30],
    //     ['name' => 'Alina', 'age' => 28],
    // ]
    ```

##### sortBySimilarity(string $query, ?string $key = null): array

- **Description:** Sorts the matched items by similarity in descending order. This method is typically used after a
  search to rank the results based on how closely they match the query.
- **Parameters:**
    - `string $query`: The search query string.
    - `?string $key`: The key to search within if items are associative arrays. Defaults to `null`.
- **Returns:** `array` - An array of matched items sorted by similarity.
- **Example:**

    ```
    $arrh = new Arrhae(['apple', 'aple', 'apply', 'banana']);
    $results = $arrh->fuzzyMatch('apple', 60, 'name')->sortBySimilarity('apple', 'name');
    // Returns items sorted by similarity to 'apple'
    ```

**Abstract Methods:**

- **getItems(): array**
    - **Description:** Retrieves all items in the collection.

- **setItems(array $items): static**
    - **Description:** Sets the collection's items to the provided array and returns the instance for method chaining.

**Usage Scenarios:**

- **User-Friendly Search Interfaces:**
    - Implementing fuzzy search to handle user typos and variations in search queries.
    - **Example:** Searching for products in an e-commerce platform where users might misspell product names.

        ```
        $products = new Arrhae([
            ['name' => 'Wireless Mouse'],
            ['name' => 'Wired Keyboard'],
            ['name' => 'USB-C Cable'],
            ['name' => 'Wireless Charger'],
        ]);
        
        // Fuzzy search for 'wirless mause'
        $searchResults = $products->fuzzyMatch('wirless mause', 80, 'name');
        // Returns ['Wireless Mouse']
        ```

- **Customer Record Deduplication:**
    - Identifying and merging duplicate customer records that may have slight variations in names.

        ```
        $customers = new Arrhae([
            ['name' => 'John Smith'],
            ['name' => 'Jon Smyth'],
            ['name' => 'Johnny Smith'],
            ['name' => 'Alice Johnson'],
        ]);
        
        // Phonetic match for 'John Smith'
        $duplicateCustomers = $customers->phoneticMatch('John Smith', 'name');
        // Returns ['John Smith', 'Jon Smyth', 'Johnny Smith']
        ```

- **Content Recommendation Systems:**
    - Ranking content based on user queries to provide the most relevant recommendations.

        ```
        $articles = new Arrhae([
            ['title' => 'Introduction to PHP'],
            ['title' => 'Advanced PHP Techniques'],
            ['title' => 'PHP for Beginners'],
            ['title' => 'JavaScript Essentials'],
        ]);
        
        // Fuzzy match and sort by similarity for query 'PHP Intro'
        $recommended = $articles->fuzzyMatch('PHP Intro', 60, 'title')->sortBySimilarity('PHP Intro', 'title');
        // Returns ['Introduction to PHP', 'PHP for Beginners']
        ```

- **Log Analysis Tools:**
    - Searching through logs for entries that match specific patterns or similar error messages.

        ```
        $logs = new Arrhae([
            ['message' => 'Error: Database connection failed'],
            ['message' => 'Warning: Low disk space'],
            ['message' => 'Error: Unable to load configuration'],
            ['message' => 'Info: User login successful'],
        ]);
        
        // Regex search for error messages
        $errorLogs = $logs->regexSearch('/^Error:/', 'message');
        // Returns [
        //     ['message' => 'Error: Database connection failed'],
        //     ['message' => 'Error: Unable to load configuration']
        // ]
        ```

- **Dynamic Filtering in Dashboards:**
    - Allowing users to filter dashboard widgets based on partial matches or specific patterns.

        ```
        $widgets = new Arrhae([
            ['name' => 'Sales Chart'],
            ['name' => 'User Growth Graph'],
            ['name' => 'Revenue Table'],
            ['name' => 'Inventory List'],
        ]);
        
        // Partial match for widgets containing 'Chart'
        $chartWidgets = $widgets->partialMatch('Chart', 'name');
        // Returns [['name' => 'Sales Chart']]
        ```

- **Social Media Content Moderation:**
    - Identifying and filtering out posts or comments that contain certain keywords or patterns.

        ```
        $posts = new Arrhae([
            ['content' => 'This is a great day!'],
            ['content' => 'I hate this product.'],
            ['content' => 'Absolutely love it!'],
            ['content' => 'Terrible service provided.'],
        ]);
        
        // Fuzzy search for negative sentiment keywords
        $negativePosts = $posts->fuzzyMatch('hate terrible', 80, 'content');
        // Returns ['I hate this product.', 'Terrible service provided.']
        ```

**Error Handling:**

1. **Invalid Threshold Values:**
    - Methods that accept a threshold parameter will throw an `InvalidArgumentException` if the threshold is not within
      the `0` to `100` range.
    - **Example:**

        ```
        $arrh = new Arrhae(['apple', 'banana']);
        try {
            $arrh->fuzzyMatch('apple', 150);
        } catch (InvalidArgumentException $e) {
            echo $e->getMessage(); // Outputs: 'Threshold must be between 0 and 100.'
        }
        ```

2. **Invalid Regular Expressions:**
    - The `regexSearch` method will throw an `InvalidArgumentException` if an invalid regex pattern is provided.
    - **Example:**

        ```
        $arrh = new Arrhae(['apple', 'banana']);
        try {
            $arrh->regexSearch('/invalid[');
        } catch (InvalidArgumentException $e) {
            echo $e->getMessage(); // Outputs: 'Invalid regular expression pattern.'
        }
        ```

3. **Negative Levenshtein Distance:**
    - The `levenshteinSearch` method will throw an `InvalidArgumentException` if the `maxDistance` parameter is
      negative.
    - **Example:**

        ```
        $arrh = new Arrhae(['apple', 'banana']);
        try {
            $arrh->levenshteinSearch('apple', -1);
        } catch (InvalidArgumentException $e) {
            echo $e->getMessage(); // Outputs: 'Maximum distance cannot be negative.'
        }
        ```

**Additional Notes:**

- **Case Insensitivity:**
    - All string comparisons within the trait are case-insensitive, ensuring that searches are not affected by the case
      of the input strings.

- **Immutable Operations:**
    - All search and filter methods return new instances of the **Arrhae** class, preserving the original collection and
      adhering to immutability principles.

- **Integration with Other Traits:**
    - The **AdvancedStringSearchTrait** seamlessly integrates with other traits in the **Arrhae** class, allowing for
      combined functionalities such as filtering, mapping, and sorting in a fluent and chainable manner.

**Conclusion:**

The **AdvancedStringSearchTrait** significantly enhances the **Arrhae** class by providing a suite of powerful and
flexible string search functionalities. Its integration allows developers to implement intelligent search mechanisms
that can handle a variety of real-world scenarios, from user-friendly search interfaces to complex data filtering and
analysis tasks. By leveraging fuzzy matching, similarity scoring, and customizable search criteria, this trait ensures
that the **Arrhae** class remains a versatile and indispensable tool for advanced data handling and manipulation in PHP
applications.

---

### Abstract Methods

The following abstract methods must be implemented by classes using various traits to ensure proper functionality:

- **getItems(): array**
    - **Description:** Retrieves all items in the collection.

- **setItems(array $items): static**
    - **Description:** Sets the collection's items to the provided array and returns the instance for method chaining.

- **map(Closure $callback): static**
    - **Description:** Applies a callback to each item in the collection, transforming the items and returning a new
      instance with the transformed items.

- **toArray(): array**
    - **Description:** Converts the collection into an array representation.

- **count(): int**
    - **Description:** Returns the number of items in the collection.

---

### Usage Scenarios

**User-Friendly Search Interfaces:**

- Implementing fuzzy search to handle user typos and variations in search queries.
- **Example:** Searching for products in an e-commerce platform where users might misspell product names.

**Data Cleaning and Validation:**

- Using phonetic matches to identify and merge duplicate records that sound similar but are spelled differently.
- **Example:** Merging customer records where names like "Smith" and "Smyth" refer to the same individual.

**Advanced Filtering in Reports:**

- Applying similarity-based searches to generate reports that include closely related data points.
- **Example:** Generating sales reports that include products with names similar to top-selling items.

**Dynamic Query Adjustments:**

- Utilizing customMatch for complex filtering criteria that are not covered by standard search methods.
- **Example:** Filtering user profiles based on a combination of multiple attributes such as age, location, and
  interests.

**Enhanced Data Exploration Tools:**

- Implementing regex searches to allow for pattern-based data exploration.
- **Example:** Extracting all email addresses from a dataset using a regex pattern.

**Phonetic Search in Contact Lists:**

- Enabling users to search contacts by how names sound rather than exact spelling.
- **Example:** Searching for "Jon" and retrieving "John" and "Jonathan".

**Sorting Search Results by Relevance:**

- Ranking search results based on their similarity to the query to prioritize more relevant items.
- **Example:** Displaying the most relevant articles at the top of a search results page.

---

### Dependencies

**FuzzyWuzzy PHP Package:**

The trait relies on the FuzzyWuzzy PHP package for calculating similarity ratios between strings.

**Installation:**

```bash
composer require wyndow/fuzzywuzzy
```

**PHP Version:**

Requires PHP 8.0 or newer due to the use of named arguments and other modern PHP features.

---

### Benefits

**Flexibility:**

Offers a wide range of string search methods catering to various use cases, enhancing the versatility of the Arrhae
class.

**Intelligent Searching:**

Incorporates fuzzy matching and similarity calculations to provide more accurate and user-friendly search results.

**Performance:**

Efficiently handles large datasets by filtering and sorting only relevant items based on search criteria.

**Chainability:**

Supports method chaining, allowing developers to build complex search and sort pipelines in a readable and maintainable
manner.

**Extensibility:**

Facilitates the addition of custom search logic through callback-based methods, enabling tailored search functionalities
as needed.

---

### Error Handling

**Invalid Threshold Values:**

Methods that accept a threshold parameter will throw an `InvalidArgumentException` if the threshold is not within the
`0` to `100` range.

**Example:**

```
$arrh = new Arrhae(['apple', 'banana']);
try {
    $arrh->fuzzyMatch('apple', 150);
} catch (InvalidArgumentException $e) {
    echo $e->getMessage(); // Outputs: 'Threshold must be between 0 and 100.'
}
```

**Invalid Regular Expressions:**

The `regexSearch` method will throw an `InvalidArgumentException` if an invalid regex pattern is provided.

**Example:**

```
$arrh = new Arrhae(['apple', 'banana']);
try {
    $arrh->regexSearch('/invalid[');
} catch (InvalidArgumentException $e) {
    echo $e->getMessage(); // Outputs: 'Invalid regular expression pattern.'
}
```

**Negative Levenshtein Distance:**

The `levenshteinSearch` method will throw an `InvalidArgumentException` if the `maxDistance` parameter is negative.

**Example:**

```
$arrh = new Arrhae(['apple', 'banana']);
try {
    $arrh->levenshteinSearch('apple', -1);
} catch (InvalidArgumentException $e) {
    echo $e->getMessage(); // Outputs: 'Maximum distance cannot be negative.'
}
```

---

### Additional Notes

**Case Insensitivity:**

All string comparisons within the trait are case-insensitive, ensuring that searches are not affected by the case of the
input strings.

**Immutable Operations:**

All search and filter methods return new instances of the **Arrhae** class, preserving the original collection and
adhering to immutability principles.

**Integration with Other Traits:**

The **AdvancedStringSearchTrait** seamlessly integrates with other traits in the **Arrhae** class, allowing for combined
functionalities such as filtering, mapping, and sorting in a fluent and chainable manner.

---

StringManipulationTrait.php
Purpose:

The StringManipulationTrait enhances the Arrhae class with advanced string transformation capabilities. It enables
fluent and immutable operations for manipulating string values within the collection.

Key Responsibilities:

Provide a fluent API for transforming string values (uppercase, lowercase, camelCase, title case, trimming, etc.).

Allow transformations on nested associative structures using a specified key.

Preserve immutability by returning new instances after each transformation.

implode(string $glue = ''): string
Description: Concatenates all string items in the collection using a delimiter.

Throws: InvalidArgumentException if any item is not a string.

Example:

php
Copy
Edit
$arrh = Arrhae::make(['one', 'two', 'three']);
echo $arrh->implode(', '); // "one, two, three"
uppercase(?string $key = null): static
Description: Converts all string items to uppercase. Optionally targets a specific key in nested arrays.

Example:

php
Copy
Edit
$arrh = Arrhae::make(['one', 'two']);
$arrh->uppercase(); // ['ONE', 'TWO']
lowercase(?string $key = null): static
Description: Converts all string items to lowercase. Optionally targets a specific key in nested arrays.

Example:

php
Copy
Edit
$arrh = Arrhae::make(['HELLO', 'WORLD']);
$arrh->lowercase(); // ['hello', 'world']
title(?string $key = null): static
Description: Converts the first letter of each word in string items to uppercase (title case).

Example:

php
Copy
Edit
$arrh = Arrhae::make(['php is great']);
$arrh->title(); // ['Php Is Great']
trim(string $characters = self::DEFAULT_TRIM_CHARACTERS, ?string $key = null): static
Description: Trims whitespace or custom characters from the beginning and end of each string item.

Example:

php
Copy
Edit
$arrh = Arrhae::make([' apple ', "\tbanana\n"]);
$arrh->trim(); // ['apple', 'banana']
camelCase(?string $key = null): static
Description: Converts string items to camelCase. Underscores, dashes, and spaces are normalized.

Example:

php
Copy
Edit
$arrh = Arrhae::make(['hello_world', 'php is great']);
$arrh->camelCase(); // ['helloWorld', 'phpIsGreat']
Integration Requirements
getItems(): array

map(Closure $callback): static

These must be implemented in the class using this trait (e.g., Arrhae), enabling internal string transformation logic.

### Conclusion

The **Arrhae** class, augmented by its extensive suite of traits, offers a powerful and flexible framework for advanced
data manipulation and transformation in PHP applications. Each trait contributes specialized functionalities, enabling
developers to handle complex data structures with ease and efficiency. This documentation serves as a comprehensive
guide to understanding and utilizing the full capabilities of the **Arrhae** class and its associated traits.

For further assistance or inquiries about integrating other functionalities into the **Arrhae** class, feel free to
reach out!