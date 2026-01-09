<?php

declare(strict_types=1);

use Avax\Migrations\BaseMigration;
use Avax\Migrations\Design\BaseMigration;
use Avax\Migrations\Design\Table\Blueprint;

/**
 * Example migration demonstrating the complete migration engine.
 *
 * Generated with: php migrate make create_products_table --create=products
 */
return new class extends BaseMigration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() : void
    {
        $this->create(table: 'products', callback: function (Blueprint $table) {
            // Primary key
            $table->id();

            // Basic information
            $table->string(name: 'name', length: 255)->unique();
            $table->string(name: 'slug', length: 255)->unique()->index();
            $table->text(name: 'description')->nullable();
            $table->mediumText(name: 'full_description')->nullable();

            // Pricing (use DECIMAL for exact precision!)
            $table->decimal(name: 'price', precision: 10, scale: 2)->unsigned();
            $table->decimal(name: 'discount_percent', precision: 5, scale: 2)->unsigned()->default(value: 0);
            $table->decimal(name: 'cost', precision: 10, scale: 2)->unsigned()->nullable();

            // Inventory
            $table->integer(name: 'stock')->unsigned()->default(value: 0);
            $table->integer(name: 'reserved')->unsigned()->default(value: 0);
            $table->tinyInteger(name: 'min_order_qty')->unsigned()->default(value: 1);

            // Status & Flags
            $table->enum(name: 'status', values: ['draft', 'active', 'archived'])->default(value: 'draft');
            $table->boolean(name: 'is_featured')->default(value: false);
            $table->boolean(name: 'is_digital')->default(value: false);
            $table->boolean(name: 'requires_shipping')->default(value: true);

            // Categorization
            $table->set(name: 'tags', values: ['new', 'sale', 'bestseller', 'limited'])->nullable();

            // Metadata (JSON for flexible attributes)
            $table->json(name: 'attributes')->nullable(); // Color, size, material, etc.
            $table->json(name: 'seo_meta')->nullable();   // SEO title, description, keywords
            $table->json(name: 'shipping_info')->nullable();

            // External identifiers
            $table->uuid(name: 'external_id')->unique();
            $table->string(name: 'sku', length: 100)->unique();
            $table->string(name: 'barcode', length: 50)->nullable()->unique();

            // Foreign keys
            $table->bigInteger(name: 'category_id')->unsigned()
                ->references(table: 'categories', column: 'id', onDelete: 'CASCADE');
            $table->bigInteger(name: 'brand_id')->unsigned()->nullable()
                ->references(table: 'brands', column: 'id', onDelete: 'SET NULL');
            $table->bigInteger(name: 'created_by')->unsigned()->nullable()
                ->references(table: 'users', column: 'id', onDelete: 'SET NULL');

            // Timestamps
            $table->timestamps();                      // created_at, updated_at
            $table->softDeletes();                     // deleted_at
            $table->timestamp(name: 'published_at')->nullable();

            // Tracking & Analytics
            $table->integer(name: 'views')->unsigned()->default(value: 0)
                ->comment(text: 'Total product page views');
            $table->integer(name: 'sales_count')->unsigned()->default(value: 0)
                ->comment(text: 'Total number of sales');
            $table->decimal(name: 'avg_rating', precision: 3, scale: 2)->unsigned()->nullable()
                ->comment(text: 'Average customer rating (0.00 to 5.00)');

            // Auto-updating timestamp
            $table->timestamp(name: 'last_modified')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() : void
    {
        $this->drop(table: 'products');
    }
};
