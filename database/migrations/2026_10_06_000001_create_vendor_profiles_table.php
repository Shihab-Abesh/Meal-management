<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The catering business that owns this installation. The application is
     * single-vendor, so this table holds exactly one row.
     */
    public function up(): void
    {
        Schema::create('vendor_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('business_name', 150);
            $table->string('organization_name', 150)->nullable();
            $table->string('owner_name', 100);
            $table->string('phone', 20);
            $table->string('email', 150);
            $table->string('address', 500);
            $table->string('city', 100);
            $table->string('logo_path')->nullable();
            $table->string('business_type', 100)->nullable();
            $table->string('vat_tin', 50)->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_profiles');
    }
};
