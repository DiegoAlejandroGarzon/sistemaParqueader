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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('plate');
            $table->foreignId('vehicle_type_id')->constrained();
            $table->dateTime('entry_at');
            $table->dateTime('exit_at')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('status')->default('PENDING');
            $table->foreignId('user_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
