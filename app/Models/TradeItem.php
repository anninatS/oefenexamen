<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TradeItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'trade_request_id',
        'inventory_id',
    ];

    /**
     * Get the trade request that includes this item.
     */
    public function tradeRequest(): BelongsTo
    {
        return $this->belongsTo(TradeRequest::class);
    }

    /**
     * Get the inventory item that is being traded.
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }
}
