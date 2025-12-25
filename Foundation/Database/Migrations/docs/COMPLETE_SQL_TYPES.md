# Complete SQL Data Types Reference

**Production-ready reference** for all 60+ SQL data types supported by the Avax Migration system.

Covers: **MySQL**, **PostgreSQL**, **SQL Server**, **SQLite**

---

## 📊 Quick Reference Table

| Category        | Count | Examples                                     |
|-----------------|-------|----------------------------------------------|
| **Numeric**     | 12    | TINYINT, INT, BIGINT, DECIMAL, FLOAT, SERIAL |
| **String**      | 11    | VARCHAR, TEXT, CHAR, NVARCHAR, TINYTEXT      |
| **Binary**      | 9     | BINARY, BLOB, VARBINARY, BYTEA               |
| **Date/Time**   | 7     | DATE, DATETIME, TIMESTAMP, INTERVAL          |
| **JSON**        | 2     | JSON, JSONB                                  |
| **Enum/Set**    | 2     | ENUM, SET                                    |
| **GIS/Spatial** | 5     | POINT, POLYGON, GEOMETRY                     |
| **PostgreSQL**  | 7     | INET, CIDR, UUID, TSVECTOR                   |
| **SQL Server**  | 4     | MONEY, UNIQUEIDENTIFIER, ROWVERSION          |
| **Special**     | 4     | XML, UUID, BIT                               |

**Total: 63 SQL Types**

---

## 1️⃣ Numeric Types (12)

### Integer Types

```php
// Tiny integer (-128 to 127, or 0 to 255 unsigned)
$table->tinyInteger('status')->unsigned()->default(0);

// Small integer (-32K to 32K)
$table->smallInteger('priority')->default(1);

// Medium integer (MySQL, -8M to 8M)
$table->mediumInteger('counter')->unsigned();

// Standard integer (-2B to 2B)
$table->integer('views')->unsigned()->default(0);

// Big integer (-9 quintillion to 9 quintillion)
$table->bigInteger('user_id')->unsigned();

// Auto-increment primary key (BIGINT)
$table->id();

// PostgreSQL auto-increment INT
$table->serial('sequence_id');

// PostgreSQL auto-increment BIGINT
$table->bigSerial('big_sequence_id');
```

**PHP Mapping:** `int`

---

### Decimal/Float Types

```php
// Exact precision (for money!)
$table->decimal('price', 10, 2)->unsigned(); // 99999999.99

// Single precision float
$table->float('rating')->default(0.0);

// Double precision float
$table->double('latitude')->nullable();

// Real number (alias for FLOAT)
$table->real('measurement');
```

**PHP Mapping:**

- `DECIMAL/NUMERIC` → `string` (preserves precision)
- `FLOAT/DOUBLE/REAL` → `float`

---

### Boolean

```php
$table->boolean('is_active')->default(true);
$table->boolean('is_verified')->default(false);
```

**PHP Mapping:** `bool`

---

## 2️⃣ String Types (11)

```php
// Variable-length string
$table->string('email', 255)->unique();

// Fixed-length string
$table->char('country_code', 2)->default('US');

// Text variants
$table->tinyText('short_note');      // Up to 255 bytes
$table->text('description');          // Up to 64KB
$table->mediumText('article');        // Up to 16MB
$table->longText('book_content');     // Up to 4GB

// Unicode strings (SQL Server)
$table->nchar('unicode_code', 10);
$table->nvarchar('unicode_name', 255);
$table->ntext('unicode_content');
```

**PHP Mapping:** `string`

---

## 3️⃣ Binary Types (9)

```php
// Fixed-length binary
$table->binary('hash', 64);

// Variable-length binary
$table->varbinary('token', 255);

// Binary Large Objects
$table->tinyBlob('tiny_file');    // Up to 255 bytes
$table->blob('file_data');         // Up to 64KB
$table->mediumBlob('image');       // Up to 16MB
$table->longBlob('video');         // Up to 4GB

// PostgreSQL binary
$table->bytea('binary_data');

// Bit field
$table->bit('flags', 8);
```

**PHP Mapping:** `string` (base64 encoded)

---

## 4️⃣ Date & Time Types (7)

```php
// Date only (YYYY-MM-DD)
$table->date('birth_date')->nullable();

// Date + Time
$table->datetime('published_at')->nullable();

// Timestamp (with timezone)
$table->timestamp('verified_at')->useCurrent();

// Time only (HH:MM:SS)
$table->time('opening_time');

// Year (YYYY)
$table->year('year_established');

// PostgreSQL interval (duration)
$table->interval('duration');

// Helper methods
$table->timestamps();              // created_at, updated_at
$table->softDeletes();             // deleted_at
```

**PHP Mapping:**

- `DATE/DATETIME/TIMESTAMP/TIME` → `\DateTimeImmutable`
- `YEAR` → `int`
- `INTERVAL` → `\DateInterval`

---

## 5️⃣ JSON Types (2)

```php
// Standard JSON
$table->json('metadata')->nullable();
$table->json('settings')->default('{}');

// PostgreSQL binary JSON (faster, indexable)
$table->jsonb('config')->nullable();
```

**PHP Mapping:** `array` (or custom DTO)
**PHPDoc:** `array<string, mixed>`

---

## 6️⃣ Enum & Set (2)

```php
// Single choice
$table->enum('status', ['draft', 'published', 'archived'])->default('draft');
$table->enum('role', ['admin', 'editor', 'viewer']);

// Multiple choices
$table->set('permissions', ['read', 'write', 'delete'])->nullable();
$table->set('tags', ['tech', 'business', 'health']);
```

**PHP Mapping:**

- `ENUM` → `string` (or PHP 8.1+ Enum)
- `SET` → `array<int, string>`

---

## 7️⃣ UUID / Identifiers (3)

```php
// UUID as CHAR(36)
$table->uuid('external_id')->unique();

// PostgreSQL native UUID
$table->uuidNative('pg_uuid')->unique();

// SQL Server GUID
$table->uniqueIdentifier('guid');
```

**PHP Mapping:** `string`
**Value Object:** `Uuid`

---

## 8️⃣ GIS / Spatial Types (5)

```php
// Point coordinates
$table->point('location');

// Line/path
$table->lineString('route');

// Area/boundary
$table->polygon('region');

// Generic geometry
$table->geometry('shape');

// Geodetic coordinates (earth-surface)
$table->geography('geo_location');
```

**PHP Mapping:** `array`
**PHPDoc:**

- `POINT` → `array{x: float, y: float}`
- `LINESTRING` → `array<int, array{x: float, y: float}>`
- `POLYGON` → `array<int, array<int, array{x: float, y: float}>>`

**Value Objects:** `GeoPoint`, `GeoPolygon`, `Geometry`

---

## 9️⃣ PostgreSQL Specific (7)

```php
// IP addresses (IPv4/IPv6)
$table->inet('ip_address');

// Network range (CIDR notation)
$table->cidr('network_range');

// MAC address
$table->macaddr('mac_address');

// Full-text search vector
$table->tsvector('search_vector');

// Full-text search query
$table->tsquery('search_query');

// Native UUID
$table->uuidNative('uuid');

// Time interval
$table->interval('duration');
```

**PHP Mapping:** `string` (except INTERVAL → `\DateInterval`)
**Value Objects:** `IpAddress`, `NetworkRange`, `MacAddress`

---

## 🔟 SQL Server Specific (4)

```php
// Currency types
$table->money('amount');
$table->smallMoney('small_amount');

// GUID (UUID)
$table->uniqueIdentifier('guid');

// Row versioning (auto-updated)
$table->rowVersion('row_version');
```

**PHP Mapping:** `string`
**Value Object:** `Money`

---

## 1️⃣1️⃣ Special Types (2)

```php
// XML documents
$table->xml('xml_data');

// Bit flags
$table->bit('feature_flags', 32);
```

---

## 🎯 PHP Type Mapping Guide

### Using the Type Mapper

```php
use Avax\Migrations\TypeMapping\SQLToPHPTypeMapper;

$mapper = new SQLToPHPTypeMapper();

// Get PHP type
$phpType = $mapper->toPhpType('VARCHAR(255)');  // 'string'
$phpType = $mapper->toPhpType('BIGINT');        // 'int'
$phpType = $mapper->toPhpType('TIMESTAMP');     // 'DateTimeImmutable'

// Get PHPDoc type
$docType = $mapper->toDocBlockType('JSON', nullable: true);
// 'array<string, mixed>|null'

// Check if should use Value Object
$shouldUse = $mapper->shouldUseValueObject('UUID');  // true
$shouldUse = $mapper->shouldUseValueObject('VARCHAR');  // false

// Get suggested Value Object class
$vo = $mapper->suggestValueObject('UUID');      // 'Uuid'
$vo = $mapper->suggestValueObject('INET');      // 'IpAddress'
$vo = $mapper->suggestValueObject('MONEY');     // 'Money'
```

---

### DTO Generation Example

```php
// SQL Column: email VARCHAR(255) NOT NULL
public string $email;

// SQL Column: age INT UNSIGNED NULL
public ?int $age;

// SQL Column: price DECIMAL(10,2) NOT NULL
public string $price; // Use string to preserve precision

// SQL Column: created_at TIMESTAMP NOT NULL
public DateTimeImmutable $createdAt;

// SQL Column: metadata JSON NULL
/** @var array<string, mixed>|null */
public ?array $metadata;

// SQL Column: location POINT NOT NULL
/** @var array{x: float, y: float} */
public array $location;

// SQL Column: external_id UUID NOT NULL
public Uuid $externalId; // Using Value Object
```

---

## 📋 Complete Type Mapping Table

| SQL Type  | PHP Type            | PHPDoc Type                 | Value Object           |
|-----------|---------------------|-----------------------------|------------------------|
| TINYINT   | `int`               | `int`                       | -                      |
| SMALLINT  | `int`               | `int`                       | -                      |
| MEDIUMINT | `int`               | `int`                       | -                      |
| INT       | `int`               | `int`                       | -                      |
| BIGINT    | `int`               | `int`                       | -                      |
| SERIAL    | `int`               | `int`                       | -                      |
| BIGSERIAL | `int`               | `int`                       | -                      |
| DECIMAL   | `string`            | `string`                    | `Money` (for currency) |
| FLOAT     | `float`             | `float`                     | -                      |
| DOUBLE    | `float`             | `float`                     | -                      |
| REAL      | `float`             | `float`                     | -                      |
| BOOLEAN   | `bool`              | `bool`                      | -                      |
| VARCHAR   | `string`            | `string`                    | -                      |
| CHAR      | `string`            | `string`                    | -                      |
| TEXT      | `string`            | `string`                    | -                      |
| BINARY    | `string`            | `string`                    | -                      |
| BLOB      | `string`            | `string`                    | -                      |
| DATE      | `DateTimeImmutable` | `DateTimeImmutable`         | -                      |
| DATETIME  | `DateTimeImmutable` | `DateTimeImmutable`         | -                      |
| TIMESTAMP | `DateTimeImmutable` | `DateTimeImmutable`         | -                      |
| TIME      | `DateTimeImmutable` | `DateTimeImmutable`         | -                      |
| YEAR      | `int`               | `int`                       | -                      |
| INTERVAL  | `DateInterval`      | `DateInterval`              | -                      |
| JSON      | `array`             | `array<string, mixed>`      | Custom DTO             |
| JSONB     | `array`             | `array<string, mixed>`      | Custom DTO             |
| ENUM      | `string`            | `string`                    | PHP 8.1+ Enum          |
| SET       | `array`             | `array<int, string>`        | -                      |
| UUID      | `string`            | `string`                    | `Uuid`                 |
| POINT     | `array`             | `array{x: float, y: float}` | `GeoPoint`             |
| POLYGON   | `array`             | `array<...>`                | `GeoPolygon`           |
| INET      | `string`            | `string`                    | `IpAddress`            |
| CIDR      | `string`            | `string`                    | `NetworkRange`         |
| MACADDR   | `string`            | `string`                    | `MacAddress`           |
| MONEY     | `string`            | `string`                    | `Money`                |
| XML       | `string`            | `string`                    | -                      |

---

## 🎓 Best Practices

### 1. **Choose the Right Type**

```php
// ❌ Bad: Using BIGINT for small values
$table->bigInteger('status');

// ✅ Good: Use appropriate size
$table->tinyInteger('status')->unsigned();
```

### 2. **Always Use unsigned() for IDs**

```php
// ❌ Bad: Negative IDs possible
$table->bigInteger('user_id');

// ✅ Good: Doubles the range
$table->bigInteger('user_id')->unsigned();
```

### 3. **Use DECIMAL for Money**

```php
// ❌ Bad: Rounding errors
$table->float('price');

// ✅ Good: Exact precision
$table->decimal('price', 10, 2)->unsigned();
```

### 4. **Prefer Value Objects for Domain Concepts**

```php
// ❌ Bad: Primitive obsession
public string $email;
public string $uuid;

// ✅ Good: Type-safe Value Objects
public Email $email;
public Uuid $uuid;
```

### 5. **Use Native Types When Available**

```php
// PostgreSQL
$table->uuidNative('id')->primary(); // Better than CHAR(36)
$table->inet('ip_address');           // Better than VARCHAR
$table->jsonb('data');                // Better than JSON (indexable)
```

### 6. **Leverage Database-Specific Features**

```php
// PostgreSQL full-text search
$table->tsvector('search_vector');
$table->index(['search_vector'], type: 'GIN');

// MySQL spatial indexing
$table->point('location');
$table->spatialIndex('location');

// SQL Server row versioning
$table->rowVersion();
```

---

## 🚀 Migration Example: E-Commerce Product

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
            $table->mediumText('full_description')->nullable();
            
            // Pricing (exact precision!)
            $table->decimal('price', 10, 2)->unsigned();
            $table->decimal('discount_percent', 5, 2)->unsigned()->default(0);
            $table->decimal('tax_rate', 5, 2)->unsigned()->default(0);
            
            // Inventory
            $table->integer('stock')->unsigned()->default(0);
            $table->integer('reserved')->unsigned()->default(0);
            $table->tinyInteger('min_order_qty')->unsigned()->default(1);
            $table->integer('max_order_qty')->unsigned()->nullable();
            
            // Status & Flags
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_digital')->default(false);
            $table->set('tags', ['new', 'sale', 'bestseller', 'limited'])->nullable();
            
            // Metadata
            $table->json('attributes')->nullable(); // Color, size, etc.
            $table->json('seo_meta')->nullable();
            
            // External IDs
            $table->uuid('external_id')->unique();
            $table->string('sku', 100)->unique();
            $table->string('barcode', 50)->nullable()->unique();
            
            // Foreign keys
            $table->bigInteger('category_id')->unsigned()
                ->references('categories', 'id', 'CASCADE');
            $table->bigInteger('brand_id')->unsigned()->nullable()
                ->references('brands', 'id', 'SET NULL');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('published_at')->nullable();
            
            // Tracking
            $table->integer('views')->unsigned()->default(0);
            $table->integer('sales_count')->unsigned()->default(0);
            $table->decimal('avg_rating', 3, 2)->unsigned()->nullable();
        });
    }

    public function down() : void
    {
        $this->drop('products');
    }
};
```

---

**Total Supported Types: 63**
**Total Modifiers: 15+**
**Database Coverage: MySQL, PostgreSQL, SQL Server, SQLite**

🎉 **Production-ready migration system with complete SQL type support!**
