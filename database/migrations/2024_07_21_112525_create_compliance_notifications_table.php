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
        Schema::create('compliance_notifications', function (Blueprint $table) {
            $table->id();

            $table->string('scan_id', 255);
            $table->string('intended_domain', 255);
            $table->string('detected_domain', 255);
            $table->text('full_path');
            $table->timestamp('timestamp')->default(now());

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_notifications');
    }
};
