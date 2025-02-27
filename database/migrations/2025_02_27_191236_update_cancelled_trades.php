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
        $senderCancelled = DB::table('trade_requests')
            ->join('notifications', function ($join) {
                $join->on('trade_requests.receiver_id', '=', 'notifications.user_id')
                    ->where('notifications.type', '=', 'trade_rejected')
                    ->whereRaw("notifications.message LIKE '%cancelled%'");
            })
            ->where('trade_requests.status', TradeRequest::STATUS_REJECTED)
            ->get(['trade_requests.id']);

        foreach ($senderCancelled as $trade) {
            DB::table('trade_requests')
                ->where('id', $trade->id)
                ->update(['status' => TradeRequest::STATUS_CANCELLED]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('trade_requests')
            ->where('status', TradeRequest::STATUS_CANCELLED)
            ->update(['status' => TradeRequest::STATUS_REJECTED]);
    }
};
