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
        Schema::table('trade_requests', function (Blueprint $table) {
            $table->foreignId('modified_by_id')->nullable()->after('receiver_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_requests', function (Blueprint $table) {
            $table->dropColumn('modified_by_id');
        });
    }
};
