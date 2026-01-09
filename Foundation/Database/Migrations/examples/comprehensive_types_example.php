<?php

declare(strict_types=1);

use Avax\Migrations\Design\Table\Blueprint;
use Avax\Migrations\Migration;
use Illuminate\Database\Migrations\Migration;

/**
 * Example migration demonstrating comprehensive SQL data type support.
 *
 * -- intent: showcase all available column types and modifiers.
 */
return new class extends Migration {
    /**
     * Execute the migration to create the example table.
     *
     * @return void
     */
    public function up() : void
    {
        $this->create(table: 'comprehensive_example', callback: function (Blueprint $table) {
            // ========================================
            // NUMERIC TYPES
            // ========================================
            $table->id(); // Auto-incrementing BIGINT primary key
            $table->tinyInteger(name: 'status')->unsigned()->default(value: 0);
            $table->smallInteger(name: 'priority')->default(value: 1);
            $table->integer(name: 'views')->unsigned()->default(value: 0);
            $table->bigInteger(name: 'large_number')->nullable();
            $table->decimal(name: 'price', precision: 10, scale: 2)->unsigned();
            $table->float(name: 'rating')->default(value: 0.0);
            $table->double(name: 'precise_value')->nullable();
            $table->boolean(name: 'is_active')->default(value: true);

            // ========================================
            // STRING TYPES
            // ========================================
            $table->string(name: 'title', length: 255)->unique();
            $table->string(name: 'slug', length: 255)->unique()->index();
            $table->char(name: 'country_code', length: 2)->default(value: 'US');
            $table->text(name: 'description')->nullable();
            $table->mediumText(name: 'content')->nullable();
            $table->longText(name: 'full_text')->nullable();
            $table->uuid(name: 'external_id')->unique();
            $table->binary(name: 'hash', length: 64)->nullable();

            // ========================================
            // DATE/TIME TYPES
            // ========================================
            $table->date(name: 'birth_date')->nullable();
            $table->datetime(name: 'published_at')->nullable();
            $table->timestamp(name: 'verified_at')->nullable();
            $table->time(name: 'opening_time')->nullable();
            $table->year(name: 'year_established')->nullable();
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at

            // ========================================
            // SPECIAL TYPES
            // ========================================
            $table->json(name: 'metadata')->nullable();
            $table->enum(name: 'type', values: ['article', 'video', 'podcast'])->default(value: 'article');
            $table->set(name: 'tags', values: ['tech', 'business', 'science', 'health'])->nullable();

            // ========================================
            // ADVANCED FEATURES
            // ========================================

            // Foreign key example
            $table->bigInteger(name: 'user_id')->unsigned()->references(table: 'users', column: 'id', onDelete: 'CASCADE');

            // Computed/Generated columns (MySQL 5.7+)
            // $table->integer(name: 'full_price')->storedAs(expression: 'price * quantity');
            // $table->string(name: 'full_name', length: 255)->virtualAs(expression: "CONCAT(first_name, ' ', last_name)");

            // Timestamp with auto-update
            $table->timestamp(name: 'last_modified')->useCurrent()->useCurrentOnUpdate();

            // Column with comment
            $table->string(name: 'internal_code', length: 50)
                ->unique()
                ->comment(text: 'Internal tracking code for analytics');
        });
    }

    /**
     * Reverse the migration by dropping the table.
     *
     * @return void
     */
    public function down() : void
    {
        $this->drop(table: 'comprehensive_example');
    }
};
