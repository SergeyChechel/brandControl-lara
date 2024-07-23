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
        Schema::create('postback_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('notification_id')->constrained('compliance_notifications');
            $table->longText('json_data');
            $table->timestamp('received_date')->default(now());

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postback_logs');
    }
};
