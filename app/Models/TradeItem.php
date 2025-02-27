<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TradeItem extends Model
{
    use HasFactory;

    const DIRECTION_OFFER = 'offer';
    const DIRECTION_REQUEST = 'request';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'trade_request_id',
        'inventory_id',
        'direction',
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

    /**
     * Check if this is an item offered by the sender.
     */
    public function isOffer(): bool
    {
        return $this->direction === self::DIRECTION_OFFER;
    }

    /**
     * Check if this is an item requested by the receiver.
     */
    public function isRequest(): bool
    {
        return $this->direction === self::DIRECTION_REQUEST;
    }
}
