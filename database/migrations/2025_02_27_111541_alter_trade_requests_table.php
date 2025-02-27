<?php

use App\Models\TradeRequest;
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
            $table->enum('status', [
                TradeRequest::STATUS_PENDING,
                TradeRequest::STATUS_ACCEPTED,
                TradeRequest::STATUS_REJECTED,
                TradeRequest::STATUS_MODIFIED // Adding MODIFIED status
            ])->default(TradeRequest::STATUS_PENDING)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_requests', function (Blueprint $table) {
            $table->enum('status', [
                TradeRequest::STATUS_PENDING,
                TradeRequest::STATUS_ACCEPTED,
                TradeRequest::STATUS_REJECTED
            ])->default(TradeRequest::STATUS_PENDING)->change();
        });
    }
};

