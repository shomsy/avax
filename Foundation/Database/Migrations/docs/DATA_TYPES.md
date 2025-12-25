# Migration Data Types Reference

Comprehensive guide to all supported SQL data types in the Avax Migration system.

---

## 📊 Numeric Types

### Integer Types

| Method                | SQL Type | Size    | Range             | Use Case              |
|-----------------------|----------|---------|-------------------|-----------------------|
| `tinyInteger($name)`  | TINYINT  | 1 byte  | -128 to 127       | Status codes, flags   |
| `smallInteger($name)` | SMALLINT | 2 bytes | -32,768 to 32,767 | Small counters        |
| `integer($name)`      | INT      | 4 bytes | -2B to 2B         | Standard integers     |
| `bigInteger($name)`   | BIGINT   | 8 bytes | -9Q to 9Q         | Large IDs, timestamps |
| `id($name = 'id')`    | BIGINT   | 8 bytes | Auto-increment    | Primary keys          |

**Modifiers:**

- `->unsigned()` - Only positive values (doubles max range)
- `->autoIncrement()` - Auto-incrementing sequence
- `->primary()` - Mark as primary key

**Examples:**

```php
$table->tinyInteger('status')->unsigned()->default(0);
$table->bigInteger('user_id')->unsigned();
$table->id(); // Auto-incrementing primary key
```

---

### Decimal Types

| Method                               | SQL Type     | Precision | Use Case                |
|--------------------------------------|--------------|-----------|-------------------------|
| `decimal($name, $precision, $scale)` | DECIMAL(p,s) | Exact     | Financial data, prices  |
| `float($name)`                       | FLOAT        | 4 bytes   | Scientific calculations |
| `double($name)`                      | DOUBLE       | 8 bytes   | High-precision floats   |

**Examples:**

```php
$table->decimal('price', 10, 2)->unsigned(); // 99999999.99
$table->float('rating')->default(0.0);
$table->double('latitude')->nullable();
```

---

### Boolean Type

| Method           | SQL Type   | Values | Use Case         |
|------------------|------------|--------|------------------|
| `boolean($name)` | TINYINT(1) | 0, 1   | True/false flags |

**Example:**

```php
$table->boolean('is_active')->default(true);
$table->boolean('is_verified')->default(false);
```

---

## 📝 String Types

### Variable Length

| Method                         | SQL Type   | Max Size     | Use Case               |
|--------------------------------|------------|--------------|------------------------|
| `string($name, $length = 255)` | VARCHAR(n) | 65,535 bytes | Names, emails, URLs    |
| `char($name, $length = 255)`   | CHAR(n)    | 255 chars    | Fixed codes (ISO, etc) |

**Examples:**

```php
$table->string('email', 255)->unique();
$table->char('country_code', 2)->default('US');
```

---

### Text Types

| Method              | SQL Type   | Max Size | Use Case               |
|---------------------|------------|----------|------------------------|
| `text($name)`       | TEXT       | 64 KB    | Descriptions, comments |
| `mediumText($name)` | MEDIUMTEXT | 16 MB    | Articles, blog posts   |
| `longText($name)`   | LONGTEXT   | 4 GB     | Books, large documents |

**Examples:**

```php
$table->text('description')->nullable();
$table->mediumText('article_content');
$table->longText('book_content');
```

---

### Special String Types

| Method                   | SQL Type  | Format            | Use Case            |
|--------------------------|-----------|-------------------|---------------------|
| `uuid($name)`            | CHAR(36)  | xxxxxxxx-xxxx-... | Unique identifiers  |
| `binary($name, $length)` | BINARY(n) | Raw bytes         | Hashes, binary data |

**Examples:**

```php
$table->uuid('external_id')->unique();
$table->binary('password_hash', 64);
```

**String Modifiers:**

- `->charset('utf8mb4')` - Character encoding
- `->collation('utf8mb4_unicode_ci')` - Sorting rules

---

## 📅 Date & Time Types

| Method             | SQL Type  | Format              | Use Case               |
|--------------------|-----------|---------------------|------------------------|
| `date($name)`      | DATE      | YYYY-MM-DD          | Birth dates, deadlines |
| `datetime($name)`  | DATETIME  | YYYY-MM-DD HH:MM:SS | Event timestamps       |
| `timestamp($name)` | TIMESTAMP | YYYY-MM-DD HH:MM:SS | Auto-updating times    |
| `time($name)`      | TIME      | HH:MM:SS            | Opening hours          |
| `year($name)`      | YEAR      | YYYY                | Year values            |

**Timestamp Helpers:**

```php
$table->timestamps(); // created_at, updated_at (both nullable)
$table->softDeletes(); // deleted_at (nullable)
```

**Timestamp Modifiers:**

- `->useCurrent()` - DEFAULT CURRENT_TIMESTAMP
- `->useCurrentOnUpdate()` - ON UPDATE CURRENT_TIMESTAMP

**Examples:**

```php
$table->date('birth_date')->nullable();
$table->datetime('published_at')->nullable();
$table->timestamp('verified_at')->useCurrent();
$table->timestamp('last_modified')->useCurrent()->useCurrentOnUpdate();
```

---

## 🎯 Special Types

### JSON

| Method         | SQL Type | Use Case                         |
|----------------|----------|----------------------------------|
| `json($name)`  | JSON     | Structured data, settings        |
| `jsonb($name)` | JSONB    | PostgreSQL binary JSON (indexed) |

**Examples:**

```php
$table->json('metadata')->nullable();
$table->json('settings')->default('{}');
```

---

### Enumerated Types

| Method                 | SQL Type  | Use Case                   |
|------------------------|-----------|----------------------------|
| `enum($name, $values)` | ENUM(...) | Single choice from list    |
| `set($name, $values)`  | SET(...)  | Multiple choices from list |

**Examples:**

```php
$table->enum('status', ['draft', 'published', 'archived'])->default('draft');
$table->enum('role', ['admin', 'editor', 'viewer'])->default('viewer');
$table->set('permissions', ['read', 'write', 'delete'])->nullable();
```

---

## 🔧 Column Modifiers

### Constraints

| Modifier                | SQL         | Description               |
|-------------------------|-------------|---------------------------|
| `->nullable()`          | NULL        | Allow NULL values         |
| `->default($value)`     | DEFAULT ... | Set default value         |
| `->unsigned()`          | UNSIGNED    | Positive numbers only     |
| `->primary()`           | PRIMARY KEY | Primary key constraint    |
| `->unique()`            | UNIQUE      | Unique constraint         |
| `->index($name = null)` | INDEX       | Add index for performance |

### Metadata

| Modifier                  | SQL               | Description            |
|---------------------------|-------------------|------------------------|
| `->comment($text)`        | COMMENT '...'     | Add column comment     |
| `->charset($charset)`     | CHARACTER SET ... | Set character encoding |
| `->collation($collation)` | COLLATE ...       | Set sorting rules      |

### Generated Columns (MySQL 5.7+)

| Modifier                   | SQL              | Description         |
|----------------------------|------------------|---------------------|
| `->storedAs($expression)`  | AS (...) STORED  | Computed and stored |
| `->virtualAs($expression)` | AS (...) VIRTUAL | Computed on-the-fly |

**Examples:**

```php
// Stored computed column
$table->decimal('total', 10, 2)->storedAs('price * quantity');

// Virtual computed column
$table->string('full_name', 255)->virtualAs("CONCAT(first_name, ' ', last_name)");
```

### Foreign Keys

| Modifier                                              | Parameters                               | Description            |
|-------------------------------------------------------|------------------------------------------|------------------------|
| `->references($table, $column, $onDelete, $onUpdate)` | table, column, CASCADE/SET NULL/RESTRICT | Foreign key constraint |

**Examples:**

```php
$table->bigInteger('user_id')->unsigned()
    ->references('users', 'id', 'CASCADE', 'CASCADE');

$table->bigInteger('category_id')->unsigned()
    ->references('categories', 'id', 'SET NULL', 'CASCADE');
```

---

## 📋 Complete Example

```php
use Avax\Migrations\Design\Table\Blueprint;
use Avax\Migrations\Migration;

return new class extends Migration
{
    public function up() : void
    {
        $this->create('products', function (Blueprint $table) {
            // Primary key
            $table->id();
            
            // Basic info
            $table->string('name', 255)->unique();
            $table->string('slug', 255)->unique()->index();
            $table->text('description')->nullable();
            
            // Pricing
            $table->decimal('price', 10, 2)->unsigned();
            $table->decimal('discount', 5, 2)->unsigned()->default(0);
            
            // Inventory
            $table->integer('stock')->unsigned()->default(0);
            $table->boolean('is_available')->default(true);
            
            // Categorization
            $table->enum('type', ['physical', 'digital', 'service'])->default('physical');
            $table->set('tags', ['new', 'sale', 'featured'])->nullable();
            
            // Metadata
            $table->json('attributes')->nullable();
            $table->uuid('external_id')->unique();
            
            // Foreign keys
            $table->bigInteger('category_id')->unsigned()
                ->references('categories', 'id', 'CASCADE');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Tracking
            $table->timestamp('last_modified')->useCurrent()->useCurrentOnUpdate();
            $table->integer('views')->unsigned()->default(0)
                ->comment('Total product views');
        });
    }

    public function down() : void
    {
        $this->drop('products');
    }
};
```

---

## 🎓 Best Practices

1. **Use appropriate types**: Don't use `BIGINT` when `INT` suffices
2. **Always use `unsigned()` for IDs**: Doubles the range for positive values
3. **Add indexes strategically**: On foreign keys and frequently queried columns
4. **Use `nullable()` explicitly**: Makes intent clear
5. **Add comments**: Document complex columns
6. **Use `enum()` for fixed sets**: Better than string validation
7. **Prefer `decimal()` for money**: Exact precision, no rounding errors
8. **Use `timestamps()` helper**: Standard audit trail
9. **Consider `softDeletes()`**: Non-destructive deletion pattern
10. **Use `uuid()` for external IDs**: Better for distributed systems

---

## 📊 Type Comparison Matrix

| Need          | Recommended Type           | Alternative                                            |
|---------------|----------------------------|--------------------------------------------------------|
| Primary Key   | `id()`                     | `bigInteger()->unsigned()->autoIncrement()->primary()` |
| Foreign Key   | `bigInteger()->unsigned()` | `integer()->unsigned()`                                |
| Money/Price   | `decimal(10, 2)`           | `integer()` (store cents)                              |
| Percentage    | `decimal(5, 2)`            | `tinyInteger()->unsigned()`                            |
| Yes/No Flag   | `boolean()`                | `tinyInteger(1)`                                       |
| Status Code   | `enum()`                   | `tinyInteger()->unsigned()`                            |
| Email         | `string(255)`              | `string(320)` (RFC max)                                |
| URL           | `string(2048)`             | `text()`                                               |
| UUID          | `uuid()`                   | `char(36)`                                             |
| Hash (SHA256) | `binary(64)`               | `char(64)`                                             |
| Short Text    | `string(255)`              | `text()`                                               |
| Article       | `mediumText()`             | `text()`                                               |
| Settings      | `json()`                   | `text()`                                               |
| Created Date  | `timestamp()`              | `datetime()`                                           |

---

**Total Supported Types: 30+**
**Total Modifiers: 15+**

Vaš migration sistem sada podržava sve standardne SQL tipove podataka! 🎉
