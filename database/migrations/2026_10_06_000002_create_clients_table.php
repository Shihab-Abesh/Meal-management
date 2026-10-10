<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Client organizations (offices) the vendor supplies meals to.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->string('contact_person', 100);
            $table->string('phone', 20);
            $table->string('email', 150)->nullable();
            $table->string('address', 500);
            $table->string('city', 100)->index();
            // Where meals are dropped off when it differs from the office address.
            $table->string('delivery_address', 500)->nullable();
            // Headcount agreed with the client; registered employees are counted separately.
            $table->unsignedInteger('employee_count')->nullable();
            $table->boolean('lunch_enabled')->default(true);
            $table->boolean('dinner_enabled')->default(false);
            $table->time('office_start_time')->nullable();
            // Null means the vendor-wide default cutoff applies.
            $table->time('lunch_cutoff_time')->nullable();
            $table->time('dinner_cutoff_time')->nullable();
            $table->text('special_instructions')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
