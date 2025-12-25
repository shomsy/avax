<?php

declare(strict_types=1);

use Avax\Migrations\BaseMigration;
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
            $table->string('name', 255)->unique();
            $table->string('slug', 255)->unique()->index();
            $table->text('description')->nullable();
            $table->mediumText('full_description')->nullable();

            // Pricing (use DECIMAL for exact precision!)
            $table->decimal('price', 10, 2)->unsigned();
            $table->decimal('discount_percent', 5, 2)->unsigned()->default(0);
            $table->decimal('cost', 10, 2)->unsigned()->nullable();

            // Inventory
            $table->integer('stock')->unsigned()->default(0);
            $table->integer('reserved')->unsigned()->default(0);
            $table->tinyInteger('min_order_qty')->unsigned()->default(1);

            // Status & Flags
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_digital')->default(false);
            $table->boolean('requires_shipping')->default(true);

            // Categorization
            $table->set('tags', ['new', 'sale', 'bestseller', 'limited'])->nullable();

            // Metadata (JSON for flexible attributes)
            $table->json('attributes')->nullable(); // Color, size, material, etc.
            $table->json('seo_meta')->nullable();   // SEO title, description, keywords
            $table->json('shipping_info')->nullable();

            // External identifiers
            $table->uuid('external_id')->unique();
            $table->string('sku', 100)->unique();
            $table->string('barcode', 50)->nullable()->unique();

            // Foreign keys
            $table->bigInteger('category_id')->unsigned()
                ->references('categories', 'id', 'CASCADE');
            $table->bigInteger('brand_id')->unsigned()->nullable()
                ->references('brands', 'id', 'SET NULL');
            $table->bigInteger('created_by')->unsigned()->nullable()
                ->references('users', 'id', 'SET NULL');

            // Timestamps
            $table->timestamps();                      // created_at, updated_at
            $table->softDeletes();                     // deleted_at
            $table->timestamp('published_at')->nullable();

            // Tracking & Analytics
            $table->integer('views')->unsigned()->default(0)
                ->comment('Total product page views');
            $table->integer('sales_count')->unsigned()->default(0)
                ->comment('Total number of sales');
            $table->decimal('avg_rating', 3, 2)->unsigned()->nullable()
                ->comment('Average customer rating (0.00 to 5.00)');

            // Auto-updating timestamp
            $table->timestamp('last_modified')->useCurrent()->useCurrentOnUpdate();
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
