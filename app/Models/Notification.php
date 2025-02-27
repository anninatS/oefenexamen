<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    /**
     * Common notification types
     */
    const TYPE_TRADE_REQUEST = 'trade_request';
    const TYPE_TRADE_ACCEPTED = 'trade_accepted';
    const TYPE_TRADE_REJECTED = 'trade_rejected';
    const TYPE_TRADE_UPDATED = 'trade_updated';
    const TYPE_ITEM_RECEIVED = 'item_received';
    const TYPE_SYSTEM = 'system';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'message',
        'read',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'read' => 'boolean',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): void
    {
        $this->update(['read' => true]);
    }

    /**
     * Mark the notification as unread.
     */
    public function markAsUnread(): void
    {
        $this->update(['read' => false]);
    }
}
