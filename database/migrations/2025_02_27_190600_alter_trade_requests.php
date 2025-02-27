<?php

use App\Models\TradeRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trade_requests', function (Blueprint $table) {
            $table->boolean('sender_approved')->nullable()->after('modified_by_id');
            $table->boolean('receiver_approved')->nullable()->after('sender_approved');

            $table->enum('status', [TradeRequest::STATUS_PENDING, TradeRequest::STATUS_MODIFIED, TradeRequest::STATUS_ACCEPTED, TradeRequest::STATUS_REJECTED, TradeRequest::STATUS_CANCELLED])
                ->default(TradeRequest::STATUS_PENDING)
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_requests', function (Blueprint $table) {
            $table->dropColumn(['sender_approved', 'receiver_approved']);

            $table->enum('status', [TradeRequest::STATUS_PENDING, TradeRequest::STATUS_ACCEPTED, TradeRequest::STATUS_REJECTED, TradeRequest::STATUS_MODIFIED])
                ->default(TradeRequest::STATUS_PENDING)
                ->change();
        });
    }
};
