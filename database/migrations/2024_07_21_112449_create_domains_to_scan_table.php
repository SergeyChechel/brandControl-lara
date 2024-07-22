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
        Schema::create('domains_to_scan', function (Blueprint $table) {
            $table->id();

            $table->string('domain', 255);
            $table->string('geo', 50)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamp('created_date')->default(now());

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domain_to_scans');
    }
};
