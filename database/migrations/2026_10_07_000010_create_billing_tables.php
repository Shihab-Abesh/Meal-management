<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bills for a client and billing period, and the payments made against
     * them.
     */
    public function up(): void
    {
        // Totals are calculated from the confirmed meals in the period at the
        // price each meal was confirmed at. Periods of one client never
        // overlap, so no meal is billed twice.
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number', 30)->nullable()->unique();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedInteger('lunch_meals')->default(0);
            $table->unsignedInteger('dinner_meals')->default(0);
            $table->unsignedInteger('total_meals')->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);
            $table->string('status', 20)->default('unpaid');
            $table->date('issue_date');
            $table->date('due_date');
            // Breakdown by meal type and unit price: [{meal_type, unit_price, quantity, amount}].
            $table->json('lines')->nullable();
            $table->string('notes', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['client_id', 'period_start']);
            $table->index(['client_id', 'period_end']);
            $table->index(['status', 'due_date']);
            $table->index('period_start');
        });

        // Removed payments are soft deleted so the money trail stays complete.
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->restrictOnDelete();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 12, 2);
            $table->string('method', 20);
            $table->string('reference', 100)->nullable();
            $table->string('note', 500)->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'payment_date']);
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bills');
    }
};
