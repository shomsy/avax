<?php

declare(strict_types=1);

use Avax\Migrations\Design\Table\Blueprint;
use Avax\Migrations\Migration;

/**
 * Comprehensive demonstration of ALL 63 supported SQL data types.
 *
 * -- intent: showcase complete type coverage across MySQL, PostgreSQL, and SQL Server.
 */
return new class extends Migration {
    /**
     * Execute the migration to create the comprehensive types table.
     *
     * @return void
     */
    public function up() : void
    {
        $this->create(table: 'all_sql_types_demo', callback: function (Blueprint $table) {
            // ========================================
            // NUMERIC TYPES (12)
            // ========================================
            $table->id(); // BIGINT AUTO_INCREMENT PRIMARY KEY
            $table->tinyInteger(name: 'tiny_int_col')->unsigned()->default(value: 0);
            $table->smallInteger(name: 'small_int_col')->default(value: 1);
            $table->mediumInteger(name: 'medium_int_col')->unsigned(); // MySQL
            $table->integer(name: 'int_col')->unsigned()->default(value: 0);
            $table->bigInteger(name: 'big_int_col')->nullable();
            $table->serial(name: 'serial_col'); // PostgreSQL
            $table->bigSerial(name: 'big_serial_col'); // PostgreSQL

            $table->decimal(name: 'decimal_col', precision: 10, scale: 2)->unsigned();
            $table->float(name: 'float_col')->default(value: 0.0);
            $table->double(name: 'double_col')->nullable();
            $table->real(name: 'real_col')->nullable();
            $table->boolean(name: 'bool_col')->default(value: true);

            // ========================================
            // STRING TYPES (11)
            // ========================================
            $table->string(name: 'varchar_col', length: 255)->unique();
            $table->char(name: 'char_col', length: 10)->default(value: 'CODE');
            $table->tinyText(name: 'tiny_text_col'); // MySQL
            $table->text(name: 'text_col')->nullable();
            $table->mediumText(name: 'medium_text_col')->nullable();
            $table->longText(name: 'long_text_col')->nullable();

            // Unicode strings (SQL Server)
            $table->nchar(name: 'nchar_col', length: 10);
            $table->nvarchar(name: 'nvarchar_col', length: 255);
            $table->ntext(name: 'ntext_col')->nullable();

            // ========================================
            // BINARY TYPES (9)
            // ========================================
            $table->binary(name: 'binary_col', length: 64);
            $table->varbinary(name: 'varbinary_col', length: 255);
            $table->blob(name: 'blob_col')->nullable();
            $table->tinyBlob(name: 'tiny_blob_col'); // MySQL
            $table->mediumBlob(name: 'medium_blob_col'); // MySQL
            $table->longBlob(name: 'long_blob_col'); // MySQL
            $table->bytea(name: 'bytea_col'); // PostgreSQL
            $table->bit(name: 'bit_col', length: 8);

            // ========================================
            // UUID / IDENTIFIERS (3)
            // ========================================
            $table->uuid(name: 'uuid_col')->unique();
            $table->uuidNative(name: 'uuid_native_col')->unique(); // PostgreSQL
            $table->uniqueIdentifier(name: 'guid_col'); // SQL Server

            // ========================================
            // DATE/TIME TYPES (7)
            // ========================================
            $table->date(name: 'date_col')->nullable();
            $table->datetime(name: 'datetime_col')->nullable();
            $table->timestamp(name: 'timestamp_col')->useCurrent();
            $table->time(name: 'time_col')->nullable();
            $table->year(name: 'year_col')->nullable();
            $table->interval(name: 'interval_col'); // PostgreSQL
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at

            // ========================================
            // JSON TYPES (2)
            // ========================================
            $table->json(name: 'json_col')->nullable();
            $table->jsonb(name: 'jsonb_col')->nullable(); // PostgreSQL

            // ========================================
            // ENUM & SET (2)
            // ========================================
            $table->enum(name: 'enum_col', values: ['option1', 'option2', 'option3'])->default(value: 'option1');
            $table->set(name: 'set_col', values: ['tag1', 'tag2', 'tag3', 'tag4'])->nullable();

            // ========================================
            // SPECIAL TYPES (1)
            // ========================================
            $table->xml(name: 'xml_col')->nullable();

            // ========================================
            // GIS / SPATIAL TYPES (5)
            // ========================================
            $table->point(name: 'point_col')->nullable();
            $table->lineString(name: 'linestring_col')->nullable();
            $table->polygon(name: 'polygon_col')->nullable();
            $table->geometry(name: 'geometry_col')->nullable();
            $table->geography(name: 'geography_col')->nullable();

            // ========================================
            // POSTGRESQL SPECIFIC (5)
            // ========================================
            $table->inet(name: 'inet_col')->nullable(); // IP address
            $table->cidr(name: 'cidr_col')->nullable(); // Network range
            $table->macaddr(name: 'macaddr_col')->nullable(); // MAC address
            $table->tsvector(name: 'tsvector_col')->nullable(); // Full-text search
            $table->tsquery(name: 'tsquery_col')->nullable(); // Full-text query

            // ========================================
            // SQL SERVER SPECIFIC (3)
            // ========================================
            $table->money(name: 'money_col')->nullable();
            $table->smallMoney(name: 'small_money_col')->nullable();
            $table->rowVersion(name: 'row_version_col'); // Auto-updated

            // ========================================
            // ADVANCED FEATURES
            // ========================================

            // Auto-updating timestamp
            $table->timestamp(name: 'last_modified')->useCurrent()->useCurrentOnUpdate();

            // Column with comment
            $table->string(name: 'internal_code', length: 50)
                ->unique()
                ->comment(text: 'Internal tracking code for analytics');

            // Unsigned integer with index
            $table->integer(name: 'indexed_value')->unsigned()->index();

            // Charset and collation (MySQL)
            $table->string(name: 'utf8_col', length: 255)
                ->charset(charset: 'utf8mb4')
                ->collation(collation: 'utf8mb4_unicode_ci');
        });
    }

    /**
     * Reverse the migration by dropping the table.
     *
     * @return void
     */
    public function down() : void
    {
        $this->drop(table: 'all_sql_types_demo');
    }
};
