<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Orders generated from confirmed meals, the riders who deliver them and
     * the delivery assignments.
     */
    public function up(): void
    {
        Schema::create('riders', function (Blueprint $table) {
            $table->id();
            // Optional login for the rider panel.
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('name', 100);
            $table->string('phone', 20)->unique();
            $table->string('nid', 30)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('emergency_contact', 150)->nullable();
            $table->string('vehicle_type', 30);
            $table->string('vehicle_number', 30)->nullable();
            $table->date('joining_date')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // One order per client, date and meal type. Quantity and amount always
        // equal the confirmed meals; the status is the single source of truth
        // for the order and its delivery.
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->nullable()->unique();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->date('order_date');
            $table->string('meal_type', 20);
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->string('delivery_address', 500);
            $table->time('delivery_time')->nullable();
            $table->text('notes')->nullable();
            $table->string('cancel_reason', 255)->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['client_id', 'order_date', 'meal_type']);
            $table->index(['order_date', 'status']);
            $table->index('status');
        });

        // What goes into each meal of the order, copied from the day's menu.
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 100);
            $table->unsignedInteger('quantity');
            $table->unsignedSmallInteger('sort_order')->default(0);
        });

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('rider_id')->constrained()->restrictOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('on_the_way_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->index(['rider_id', 'assigned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('riders');
    }
};
