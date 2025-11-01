<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('glasses_stock', function (Blueprint $table) {
            $table->id();
            $table->string('item_name'); // e.g., "Ray-Ban Aviator", "Progressive Lens"
            $table->string('item_code')->unique(); // SKU/Barcode
            $table->enum('item_type', ['frame', 'lens', 'accessory']); // Type of item
            $table->string('brand')->nullable(); // Brand name
            $table->text('description')->nullable(); // Item description
            $table->integer('quantity')->default(0); // Current stock quantity
            $table->integer('min_quantity')->default(10); // Minimum stock alert level
            $table->decimal('cost_price', 10, 2)->default(0); // Cost price per unit
            $table->decimal('selling_price', 10, 2)->default(0); // Selling price per unit
            $table->string('supplier')->nullable(); // Supplier name
            $table->string('location')->nullable(); // Storage location
            $table->timestamps();
        });

        // Create stock movements table for tracking
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('glasses_stock')->onDelete('cascade');
            $table->enum('movement_type', ['in', 'out', 'adjustment']); // Type of movement
            $table->integer('quantity'); // Quantity moved (positive or negative)
            $table->integer('quantity_before'); // Stock before movement
            $table->integer('quantity_after'); // Stock after movement
            $table->string('reference_type')->nullable(); // e.g., 'order', 'purchase', 'return'
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of related record
            $table->text('notes')->nullable(); // Additional notes
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Who made the change
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('glasses_stock');
    }
};
