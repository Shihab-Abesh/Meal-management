<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One meal for one employee on one date. Replaces the notebook count.
     *
     * A meal that is unmarked after confirmation is kept as "cancelled" so
     * edits stay traceable; only confirmed meals are counted and billed.
     * unit_price is the client's price at confirmation and never changes
     * when prices are updated later.
     */
    public function up(): void
    {
        Schema::create('meal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->date('meal_date');
            $table->string('meal_type', 20);
            $table->string('status', 20)->default('confirmed');
            $table->decimal('unit_price', 10, 2);
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Business rule: no duplicate meal for the same employee, date and meal type.
            $table->unique(['employee_id', 'meal_date', 'meal_type']);
            $table->index(['client_id', 'meal_date', 'meal_type', 'status']);
            $table->index(['meal_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_entries');
    }
};
