### **Technical Documentation for Gemini Database Migration Blueprint**
---

## **Overview**

This documentation provides a **detailed breakdown** of the **Gemini Database Migration Blueprint** module, which allows
developers to define database table structures **fluently and expressively**.

The module is composed of multiple **Traits** and the **Blueprint class**, each serving a specific role in **schema
definition, modification, and indexing**.

This documentation is structured **per file**, including:

- **File description**
- **Function breakdown**
- **Usage examples**
- **Real-world applications**

---

# 📂 **Blueprint.php**

### **File Location**

```
Gemini/Database/Migration/Blueprint/Blueprint.php
```

### **Description**

The `Blueprint` class acts as the **main entry point** for defining database tables using a **fluent API**. It allows
developers to:

- Define **columns** (`string`, `integer`, `boolean`, etc.)
- Add **constraints** (`foreign keys`, `unique`, `primary keys`)
- Apply **indexing** (composite indexes, full-text search, etc.)
- Modify **table properties** (storage engine, character set)
- Drop **columns, tables, and indexes**
- Generate **raw SQL** for migration execution

### **Usage Example**

```
$blueprint = new Blueprint('users');

$blueprint
    ->id()
    ->string('name')
    ->integer('age', true)
    ->boolean('is_active')
    ->timestamp('created_at')
    ->foreign('role_id', 'id', 'roles')->onDelete('CASCADE')
    ->index('email')
    ->engine('InnoDB')
    ->charset('utf8mb4')
    ->collation('utf8mb4_unicode_ci');

echo $blueprint->toSql();
```

#### **Generated SQL**

```sql
CREATE TABLE users
(
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(255) NOT NULL,
    age        INT UNSIGNED NOT NULL,
    is_active  TINYINT(1)   NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE,
    INDEX index_email (email)
) ENGINE = InnoDB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

---

## 📂 **Traits**

Each trait focuses on **specific table schema functionalities**.

---

# 📂 **ColumnDefinitionsTrait.php**

### **Location**

```
Gemini/Database/Migration/Blueprint/Traits/ColumnDefinitionsTrait.php
```

### **Description**

This trait **adds column definition methods** to the `Blueprint` class, allowing developers to define various **data
types** (VARCHAR, INT, BOOLEAN, JSON, etc.).

### **Key Functions**

| Function                                   | Description                                                                              | Usage Example                              |
|--------------------------------------------|------------------------------------------------------------------------------------------|--------------------------------------------|
| `id()`                                     | Creates an auto-incrementing primary key (`id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY`). | `$blueprint->id();`                        |
| `string($name, $length = 255)`             | Defines a VARCHAR column.                                                                | `$blueprint->string('username', 150);`     |
| `text($name)`                              | Defines a TEXT column.                                                                   | `$blueprint->text('bio');`                 |
| `integer($name, $unsigned = false)`        | Defines an INT column.                                                                   | `$blueprint->integer('age', true);`        |
| `bigInteger($name, $unsigned = false)`     | Defines a BIGINT column.                                                                 | `$blueprint->bigInteger('user_id', true);` |
| `boolean($name)`                           | Defines a BOOLEAN column.                                                                | `$blueprint->boolean('is_active');`        |
| `timestamp($name, $defaultCurrent = true)` | Defines a TIMESTAMP column.                                                              | `$blueprint->timestamp('created_at');`     |

---

# 📂 **ConstraintsTrait.php**

### **Location**

```
Gemini/Database/Migration/Blueprint/Traits/ConstraintsTrait.php
```

### **Description**

This trait **adds foreign key constraints** to table columns.

### **Key Functions**

| Function                                  | Description                                               | Usage Example                                                                   |
|-------------------------------------------|-----------------------------------------------------------|---------------------------------------------------------------------------------|
| `foreign($column, $references, $onTable)` | Adds a foreign key.                                       | `$blueprint->foreign('role_id', 'id', 'roles');`                                |
| `onDelete($action)`                       | Defines ON DELETE behavior (`CASCADE`, `SET NULL`, etc.). | `$blueprint->foreign('role_id', 'id', 'roles')->onDelete('CASCADE');`           |
| `onUpdate($action)`                       | Defines ON UPDATE behavior.                               | `$blueprint->foreign('category_id', 'id', 'categories')->onUpdate('RESTRICT');` |

---

# 📂 **IndexDefinitionsTrait.php**

### **Location**

```
Gemini/Database/Migration/Blueprint/Traits/IndexDefinitionsTrait.php
```

### **Description**

This trait provides **methods for indexing columns**, including **unique, full-text, spatial, and composite indexes**.

### **Key Functions**

| Function                                    | Description               | Usage Example                                 |
|---------------------------------------------|---------------------------|-----------------------------------------------|
| `index($columns, $indexName = null)`        | Creates a standard index. | `$blueprint->index('email');`                 |
| `unique($columns, $indexName = null)`       | Creates a UNIQUE index.   | `$blueprint->unique('username');`             |
| `fullText($columns, $indexName = null)`     | Creates a FULLTEXT index. | `$blueprint->fullText(['title', 'content']);` |
| `spatialIndex($columns, $indexName = null)` | Creates a SPATIAL index.  | `$blueprint->spatialIndex('location');`       |

---

# 📂 **ModifiersTrait.php**

### **Location**

```
Gemini/Database/Migration/Blueprint/Traits/ModifiersTrait.php
```

### **Description**

This trait provides **modifiers for columns**, allowing **default values, NULL constraints, computed columns, and more
**.

### **Key Functions**

| Function          | Description                          | Usage Example                                        |
|-------------------|--------------------------------------|------------------------------------------------------|
| `nullable()`      | Allows NULL values.                  | `$blueprint->string('middle_name')->nullable();`     |
| `default($value)` | Sets a default value.                | `$blueprint->integer('status')->default(1);`         |
| `useCurrent()`    | Sets `CURRENT_TIMESTAMP` as default. | `$blueprint->timestamp('created_at')->useCurrent();` |
| `after($column)`  | Positions column after another.      | `$blueprint->string('nickname')->after('name');`     |

---

# 📂 **SchemaManipulationTrait.php**

### **Location**

```
Gemini/Database/Migration/Blueprint/Traits/SchemaManipulationTrait.php
```

### **Description**

This trait **allows schema modifications**, such as **renaming, dropping columns, and indexes**.

### **Key Functions**

| Function                     | Description                | Usage Example                                  |
|------------------------------|----------------------------|------------------------------------------------|
| `rename($oldName, $newName)` | Renames a column.          | `$blueprint->rename('username', 'user_name');` |
| `dropColumn($columns)`       | Drops one or more columns. | `$blueprint->dropColumn('old_column');`        |
| `dropTable($table)`          | Drops a table.             | `$blueprint->dropTable('temp_users');`         |

---

# 📂 **SpatialColumnsTrait.php**

### **Location**

```
Gemini/Database/Migration/Blueprint/Traits/SpatialColumnsTrait.php
```

### **Description**

This trait **defines spatial (geometric) columns**, useful for **geographical data storage**.

### **Key Functions**

| Function         | Description               | Usage Example                       |
|------------------|---------------------------|-------------------------------------|
| `point($name)`   | Defines a POINT column.   | `$blueprint->point('coordinates');` |
| `polygon($name)` | Defines a POLYGON column. | `$blueprint->polygon('city_area');` |

---

# 📂 **TablePropertiesTrait.php**

### **Location**

```
Gemini/Database/Migration/Blueprint/Traits/TablePropertiesTrait.php
```

### **Description**

This trait **manages table properties** like **storage engine, charset, and comments**.

### **Key Functions**

| Function                | Description               | Usage Example                                  |
|-------------------------|---------------------------|------------------------------------------------|
| `engine($engine)`       | Defines the table engine. | `$blueprint->engine('InnoDB');`                |
| `charset($charset)`     | Sets the charset.         | `$blueprint->charset('utf8mb4');`              |
| `collation($collation)` | Sets the collation.       | `$blueprint->collation('utf8mb4_unicode_ci');` |

---

### **Final Notes**

✅ **Fluent API** allows **method chaining**.  
✅ **SQL schema generation is effortless**.  
✅ **Designed for large-scale, production-ready databases**. 🚀