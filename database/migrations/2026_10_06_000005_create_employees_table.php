<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Employees of a client organization who may take meals. Employees are
     * deactivated rather than deleted so their meal history stays intact.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            // The client's own staff ID; unique within that client only.
            $table->string('employee_code', 50);
            $table->string('name', 100);
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('department', 100)->nullable();
            $table->string('designation', 100)->nullable();
            $table->string('meal_preference', 30)->default('regular');
            $table->boolean('lunch_enabled')->default(true);
            $table->boolean('dinner_enabled')->default(false);
            $table->string('status', 20)->default('active');
            $table->date('joining_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['client_id', 'employee_code']);
            $table->index(['client_id', 'status', 'name']);
            $table->index(['client_id', 'department']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
