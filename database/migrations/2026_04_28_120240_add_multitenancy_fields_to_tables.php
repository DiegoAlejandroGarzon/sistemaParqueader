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
        // Alter Users
        Schema::table('users', function (Blueprint $table) {
            // For super-admin restricting admins
            $table->integer('max_parkings')->nullable();
            
            // For admin identifying which operator they created
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Note: we assume 'role' already exists from previous steps (admin, operator), 
            // we will just add 'super-admin' in logic, the column type is likely string.
        });

        // Alter VehicleTypes
        Schema::table('vehicle_types', function (Blueprint $table) {
            $table->foreignId('parking_id')->nullable()->constrained('parkings')->onDelete('cascade');
        });

        // Alter Rates
        Schema::table('rates', function (Blueprint $table) {
            $table->foreignId('parking_id')->nullable()->constrained('parkings')->onDelete('cascade');
        });

        // Alter Tickets
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('parking_id')->nullable()->constrained('parkings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['parking_id']);
            $table->dropColumn('parking_id');
        });
        
        Schema::table('rates', function (Blueprint $table) {
            $table->dropForeign(['parking_id']);
            $table->dropColumn('parking_id');
        });
        
        Schema::table('vehicle_types', function (Blueprint $table) {
            $table->dropForeign(['parking_id']);
            $table->dropColumn('parking_id');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
            $table->dropColumn('max_parkings');
        });
    }
};
