<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menu items, client-specific meal prices and the menu for each date.
     */
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('category', 30)->index();
            $table->string('description', 500)->nullable();
            $table->decimal('default_price', 10, 2)->default(0);
            $table->string('status', 20)->default('active')->index();
            $table->string('image_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Price per meal for one client. A new row with a later effective date
        // changes the price from that day on; earlier meals keep their price.
        Schema::create('client_menu_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->string('meal_type', 20);
            $table->decimal('price', 10, 2);
            $table->date('effective_from');
            $table->string('notes', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['client_id', 'meal_type', 'effective_from']);
        });

        Schema::create('daily_menus', function (Blueprint $table) {
            $table->id();
            $table->date('menu_date');
            $table->string('meal_type', 20);
            // Standard price for this day's meal; client prices take precedence.
            $table->decimal('price', 10, 2)->nullable();
            $table->string('notes', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['menu_date', 'meal_type']);
        });

        Schema::create('daily_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_item_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->unique(['daily_menu_id', 'menu_item_id']);
            $table->index('menu_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_menu_items');
        Schema::dropIfExists('daily_menus');
        Schema::dropIfExists('client_menu_prices');
        Schema::dropIfExists('menu_items');
    }
};
