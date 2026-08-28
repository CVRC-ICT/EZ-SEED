<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('farmers', function (Blueprint $table) {
        $table->id();

        // Farmer Information
        $table->string('rsbsa_number')->nullable();
        $table->string('first_name');
        $table->string('middle_name')->nullable();
        $table->string('last_name');
        $table->string('suffix')->nullable();

        $table->date('birth_date')->nullable();
        $table->enum('sex', ['Male', 'Female']);
        $table->string('civil_status')->nullable();

        // Contact Information
        $table->string('contact_number')->nullable();
        $table->string('email')->nullable();

        // Address
$table->string('province');
$table->string('municipality');
$table->string('barangay');

        // Farm Information
        $table->decimal('farm_area', 8, 2)->nullable();
        $table->string('tenurial_status')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farmers');
    }
};
