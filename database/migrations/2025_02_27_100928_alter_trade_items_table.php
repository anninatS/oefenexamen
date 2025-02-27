<?php

use App\Models\TradeItem;
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
        Schema::table('trade_items', function (Blueprint $table) {
            $table->enum('direction', [TradeItem::DIRECTION_OFFER, TradeItem::DIRECTION_REQUEST])
                ->default(TradeItem::DIRECTION_OFFER)
                ->after('inventory_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_items', function (Blueprint $table) {
            $table->dropColumn('direction');
        });
    }
};
