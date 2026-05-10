<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\Table\Blueprint;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\MySQLGrammar;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Comprehensive tests for the Blueprint schema design DSL.
 */
final class BlueprintTest extends TestCase
{
    private MySQLGrammar $grammar;

    #[Test]
    public function blueprint_stores_table_name() : void
    {
        $blueprint = new Blueprint(table: 'users');
        $sql       = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: 'CREATE TABLE `users`', haystack: $sql[0]);
    }

    // ============================================================
    // BASIC SETUP
    // ============================================================

    #[Test]
    public function empty_blueprint_produces_empty_table() : void
    {
        $blueprint = new Blueprint(table: 'empty');
        $sql       = $blueprint->toSql(grammar: $this->grammar);

        self::assertSame(expected: 'CREATE TABLE `empty` ()', actual: $sql[0]);
    }

    #[Test]
    public function id_creates_bigint_auto_increment_primary_key() : void
    {
        $blueprint = new Blueprint(table: 'users');
        $blueprint->id();
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`id` BIGINT', haystack: $sql[0]);
        self::assertStringContainsString(needle: 'AUTO_INCREMENT', haystack: $sql[0]);
        self::assertStringContainsString(needle: 'PRIMARY KEY', haystack: $sql[0]);
    }

    // ============================================================
    // PRIMARY KEY / ID
    // ============================================================

    #[Test]
    public function id_with_custom_name() : void
    {
        $blueprint = new Blueprint(table: 'users');
        $blueprint->id(name: 'user_id');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`user_id` BIGINT', haystack: $sql[0]);
    }

    #[Test]
    public function tiny_integer_creates_tinyint() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->tinyInteger('status');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`status` TINYINT', haystack: $sql[0]);
    }

    // ============================================================
    // NUMERIC TYPES
    // ============================================================

    #[Test]
    public function small_integer_creates_smallint() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->smallInteger('count');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`count` SMALLINT', haystack: $sql[0]);
    }

    #[Test]
    public function integer_creates_int() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->integer('quantity');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`quantity` INT', haystack: $sql[0]);
    }

    #[Test]
    public function big_integer_creates_bigint() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->bigInteger('bigint_col');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`bigint_col` BIGINT', haystack: $sql[0]);
    }

    #[Test]
    public function medium_integer_creates_mediumint() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->mediumInteger('medium_col');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`medium_col` MEDIUMINT', haystack: $sql[0]);
    }

    #[Test]
    public function decimal_with_precision_and_scale() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->decimal('price', precision: 10, scale: 2);
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`price` DECIMAL(10,2)', haystack: $sql[0]);
    }

    #[Test]
    public function decimal_with_default_precision() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->decimal('price');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`price` DECIMAL(8,2)', haystack: $sql[0]);
    }

    #[Test]
    public function float_creates_float() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->float('float_col');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`float_col` FLOAT', haystack: $sql[0]);
    }

    #[Test]
    public function double_creates_double() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->double('double_col');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`double_col` DOUBLE', haystack: $sql[0]);
    }

    #[Test]
    public function real_creates_real() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->real('real_col');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`real_col` REAL', haystack: $sql[0]);
    }

    #[Test]
    public function boolean_creates_tinyint_one() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->boolean('is_active');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`is_active` TINYINT(1)', haystack: $sql[0]);
    }

    #[Test]
    public function serial_creates_serial() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->serial('serial_col');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`serial_col` SERIAL', haystack: $sql[0]);
    }

    #[Test]
    public function big_serial_creates_bigserial() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->bigSerial('bigserial_col');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`bigserial_col` BIGSERIAL', haystack: $sql[0]);
    }

    #[Test]
    public function string_creates_varchar() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->string('name');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`name` VARCHAR(255)', haystack: $sql[0]);
    }

    // ============================================================
    // STRING TYPES
    // ============================================================

    #[Test]
    public function string_with_custom_length() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->string('email', length: 100);
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`email` VARCHAR(100)', haystack: $sql[0]);
    }

    #[Test]
    public function char_creates_fixed_length() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->char('code', length: 2);
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`code` CHAR(2)', haystack: $sql[0]);
    }

    #[Test]
    public function text_creates_text() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->text('description');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`description` TEXT', haystack: $sql[0]);
    }

    #[Test]
    public function medium_text_creates_mediumtext() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->mediumText('content');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`content` MEDIUMTEXT', haystack: $sql[0]);
    }

    #[Test]
    public function long_text_creates_longtext() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->longText('article');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`article` LONGTEXT', haystack: $sql[0]);
    }

    #[Test]
    public function tiny_text_creates_tinytext() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->tinyText('short_note');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`short_note` TINYTEXT', haystack: $sql[0]);
    }

    #[Test]
    public function uuid_creates_char_36() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->uuid('uuid_col');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`uuid_col` CHAR(36)', haystack: $sql[0]);
    }

    #[Test]
    public function uuid_native_creates_uuid() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->uuidNative('native_uuid');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`native_uuid` UUID', haystack: $sql[0]);
    }

    #[Test]
    public function date_creates_date() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->date('birth_date');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`birth_date` DATE', haystack: $sql[0]);
    }

    // ============================================================
    // DATE/TIME TYPES
    // ============================================================

    #[Test]
    public function datetime_creates_datetime() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->datetime('created');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`created` DATETIME', haystack: $sql[0]);
    }

    #[Test]
    public function timestamp_creates_timestamp() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->timestamp('updated');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`updated` TIMESTAMP', haystack: $sql[0]);
    }

    #[Test]
    public function time_creates_time() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->time('start_time');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`start_time` TIME', haystack: $sql[0]);
    }

    #[Test]
    public function year_creates_year() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->year('year_col');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`year_col` YEAR', haystack: $sql[0]);
    }

    #[Test]
    public function timestamps_creates_created_at_and_updated_at() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->timestamps();
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`created_at`', haystack: $sql[0]);
        self::assertStringContainsString(needle: '`updated_at`', haystack: $sql[0]);
    }

    #[Test]
    public function soft_deletes_creates_deleted_at() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->softDeletes();
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`deleted_at`', haystack: $sql[0]);
    }

    #[Test]
    public function soft_deletes_with_custom_name() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->softDeletes(name: 'removed_at');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`removed_at`', haystack: $sql[0]);
    }

    #[Test]
    public function json_creates_json() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->json('data');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`data` JSON', haystack: $sql[0]);
    }

    // ============================================================
    // SPECIAL TYPES
    // ============================================================

    #[Test]
    public function jsonb_creates_jsonb() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->jsonb('data');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`data` JSONB', haystack: $sql[0]);
    }

    #[Test]
    public function enum_creates_enum_with_values() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->enum('status', values: ['pending', 'active', 'inactive']);
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: "ENUM('pending','active','inactive')", haystack: $sql[0]);
    }

    #[Test]
    public function set_creates_set_with_values() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->set('permissions', values: ['read', 'write', 'delete']);
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: "SET('read','write','delete')", haystack: $sql[0]);
    }

    #[Test]
    public function xml_creates_xml() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->xml('xml_col');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`xml_col` XML', haystack: $sql[0]);
    }

    #[Test]
    public function binary_creates_binary() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->binary('hash', length: 32);
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`hash` BINARY(32)', haystack: $sql[0]);
    }

    // ============================================================
    // BINARY TYPES
    // ============================================================

    #[Test]
    public function varbinary_creates_varbinary() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->varbinary('data', length: 100);
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`data` VARBINARY(100)', haystack: $sql[0]);
    }

    #[Test]
    public function blob_creates_blob() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->blob('file_data');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`file_data` BLOB', haystack: $sql[0]);
    }

    #[Test]
    public function tiny_blob_creates_tinyblob() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->tinyBlob('tiny_data');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`tiny_data` TINYBLOB', haystack: $sql[0]);
    }

    #[Test]
    public function medium_blob_creates_mediumblob() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->mediumBlob('medium_data');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`medium_data` MEDIUMBLOB', haystack: $sql[0]);
    }

    #[Test]
    public function long_blob_creates_longblob() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->longBlob('large_data');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`large_data` LONGBLOB', haystack: $sql[0]);
    }

    #[Test]
    public function bytea_creates_bytea() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->bytea('binary_col');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`binary_col` BYTEA', haystack: $sql[0]);
    }

    #[Test]
    public function bit_creates_bit() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->bit('flags', length: 8);
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`flags` BIT(8)', haystack: $sql[0]);
    }

    #[Test]
    public function point_creates_point() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->point('location');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`location` POINT', haystack: $sql[0]);
    }

    // ============================================================
    // GIS / SPATIAL TYPES
    // ============================================================

    #[Test]
    public function line_string_creates_linestring() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->lineString('path');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`path` LINESTRING', haystack: $sql[0]);
    }

    #[Test]
    public function polygon_creates_polygon() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->polygon('area');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`area` POLYGON', haystack: $sql[0]);
    }

    #[Test]
    public function geometry_creates_geometry() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->geometry('shape');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`shape` GEOMETRY', haystack: $sql[0]);
    }

    #[Test]
    public function geography_creates_geography() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->geography('coordinate');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`coordinate` GEOGRAPHY', haystack: $sql[0]);
    }

    #[Test]
    public function inet_creates_inet() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->inet('ip_address');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`ip_address` INET', haystack: $sql[0]);
    }

    // ============================================================
    // POSTGRESQL SPECIFIC TYPES
    // ============================================================

    #[Test]
    public function cidr_creates_cidr() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->cidr('network');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`network` CIDR', haystack: $sql[0]);
    }

    #[Test]
    public function macaddr_creates_macaddr() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->macaddr('mac');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`mac` MACADDR', haystack: $sql[0]);
    }

    #[Test]
    public function tsvector_creates_tsvector() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->tsvector('search_vector');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`search_vector` TSVECTOR', haystack: $sql[0]);
    }

    #[Test]
    public function tsquery_creates_tsquery() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->tsquery('search_query');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`search_query` TSQUERY', haystack: $sql[0]);
    }

    #[Test]
    public function money_creates_money() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->money('amount');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`amount` MONEY', haystack: $sql[0]);
    }

    // ============================================================
    // SQL SERVER SPECIFIC TYPES
    // ============================================================

    #[Test]
    public function small_money_creates_smallmoney() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->smallMoney('small_amount');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`small_amount` SMALLMONEY', haystack: $sql[0]);
    }

    #[Test]
    public function unique_identifier_creates_uniqueidentifier() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->uniqueIdentifier('guid');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`guid` UNIQUEIDENTIFIER', haystack: $sql[0]);
    }

    #[Test]
    public function row_version_creates_rowversion() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->rowVersion();
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`row_version` ROWVERSION', haystack: $sql[0]);
    }

    #[Test]
    public function row_version_with_custom_name() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->rowVersion(name: 'custom_version');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`custom_version` ROWVERSION', haystack: $sql[0]);
    }

    #[Test]
    public function nullable_adds_null_constraint() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->string('name')->nullable();
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`name` VARCHAR(255) NULL', haystack: $sql[0]);
    }

    // ============================================================
    // COLUMN MODIFIERS
    // ============================================================

    #[Test]
    public function non_nullable_adds_not_null_constraint() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->string('name')->nullable(value: false);
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: '`name` VARCHAR(255) NOT NULL', haystack: $sql[0]);
    }

    #[Test]
    public function default_adds_default_value() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->string('status')->default(value: 'pending');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: "DEFAULT 'pending'", haystack: $sql[0]);
    }

    #[Test]
    public function unsigned_adds_unsigned_modifier() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->integer('count')->unsigned();
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: 'INT UNSIGNED', haystack: $sql[0]);
    }

    #[Test]
    public function unique_adds_unique_constraint() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->string('email')->unique();
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: 'UNIQUE', haystack: $sql[0]);
    }

    #[Test]
    public function comment_adds_comment() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->string('name')->comment(text: 'User full name');
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: "COMMENT 'User full name'", haystack: $sql[0]);
    }

    #[Test]
    public function use_current_adds_default_current_timestamp() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->timestamp('created')->useCurrent();
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: 'DEFAULT CURRENT_TIMESTAMP', haystack: $sql[0]);
    }

    #[Test]
    public function use_current_on_update_adds_on_update() : void
    {
        $blueprint = new Blueprint(table: 'test');
        $blueprint->timestamp('updated')->useCurrentOnUpdate();
        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: 'ON UPDATE CURRENT_TIMESTAMP', haystack: $sql[0]);
    }

    #[Test]
    public function set_alter_mode_generates_alter_statements() : void
    {
        $blueprint = new Blueprint(table: 'users');
        $blueprint->setAlterMode();
        $blueprint->string('new_column');

        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: 'ALTER TABLE `users` ADD', haystack: $sql[0]);
    }

    // ============================================================
    // ALTER MODE
    // ============================================================

    #[Test]
    public function drop_column_generates_drop_statement() : void
    {
        $blueprint = new Blueprint(table: 'users');
        $blueprint->setAlterMode();
        $blueprint->dropColumn('old_column');

        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: 'ALTER TABLE `users` DROP COLUMN `old_column`', haystack: $sql[0]);
    }

    #[Test]
    public function drop_multiple_columns() : void
    {
        $blueprint = new Blueprint(table: 'users');
        $blueprint->setAlterMode();
        $blueprint->dropColumn('col1', 'col2', 'col3');

        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertCount(expectedCount: 3, haystack: $sql);
        self::assertStringContainsString(needle: 'DROP COLUMN `col1`', haystack: $sql[0]);
        self::assertStringContainsString(needle: 'DROP COLUMN `col2`', haystack: $sql[1]);
        self::assertStringContainsString(needle: 'DROP COLUMN `col3`', haystack: $sql[2]);
    }

    #[Test]
    public function rename_column_generates_rename_statement() : void
    {
        $blueprint = new Blueprint(table: 'users');
        $blueprint->setAlterMode();
        $blueprint->renameColumn(from: 'old_name', to: 'new_name');

        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertStringContainsString(needle: 'ALTER TABLE `users` RENAME COLUMN `old_name` TO `new_name`', haystack: $sql[0]);
    }

    #[Test]
    public function alter_mode_combines_add_and_drop() : void
    {
        $blueprint = new Blueprint(table: 'users');
        $blueprint->setAlterMode();
        $blueprint->string('new_column');
        $blueprint->dropColumn('old_column');

        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertCount(expectedCount: 2, haystack: $sql);
        self::assertStringContainsString(needle: 'ADD', haystack: $sql[0]);
        self::assertStringContainsString(needle: 'DROP COLUMN', haystack: $sql[1]);
    }

    #[Test]
    public function multiple_columns_produce_single_create_statement() : void
    {
        $blueprint = new Blueprint(table: 'users');
        $blueprint->id();
        $blueprint->string('name');
        $blueprint->string('email');
        $blueprint->timestamps();

        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertCount(expectedCount: 1, haystack: $sql);
        self::assertStringContainsString(needle: '`id`', haystack: $sql[0]);
        self::assertStringContainsString(needle: '`name`', haystack: $sql[0]);
        self::assertStringContainsString(needle: '`email`', haystack: $sql[0]);
        self::assertStringContainsString(needle: '`created_at`', haystack: $sql[0]);
        self::assertStringContainsString(needle: '`updated_at`', haystack: $sql[0]);
    }

    // ============================================================
    // MULTIPLE COLUMNS
    // ============================================================

    #[Test]
    public function complete_users_table() : void
    {
        $blueprint = new Blueprint(table: 'users');
        $blueprint->id();
        $blueprint->string('name');
        $blueprint->string('email')->unique();
        $blueprint->string('password');
        $blueprint->string('status')->default(value: 'active');
        $blueprint->timestamps();
        $blueprint->softDeletes();

        $sql = $blueprint->toSql(grammar: $this->grammar);

        self::assertCount(expectedCount: 1, haystack: $sql);
        self::assertStringContainsString(needle: 'CREATE TABLE `users`', haystack: $sql[0]);
        self::assertStringContainsString(needle: 'AUTO_INCREMENT PRIMARY KEY', haystack: $sql[0]);
        self::assertStringContainsString(needle: 'UNIQUE', haystack: $sql[0]);
        self::assertStringContainsString(needle: "'active'", haystack: $sql[0]);
        self::assertStringContainsString(needle: 'NULL', haystack: $sql[0]); // timestamps and softDeletes are nullable
    }

    protected function setUp() : void
    {
        $this->grammar = new MySQLGrammar();
    }
}
