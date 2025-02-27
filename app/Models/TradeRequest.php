<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TradeRequest extends Model
{
    use HasFactory;

    /**
     * The possible statuses for a trade request.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_MODIFIED = 'modified';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'status',
        'modified_by_id',
        'sender_approved',
        'receiver_approved',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sender_approved' => 'boolean',
        'receiver_approved' => 'boolean',
    ];

    /**
     * Get the sender of the trade request.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the receiver of the trade request.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the user who last modified the trade.
     */
    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by_id');
    }

    /**
     * Get the items included in this trade request.
     */
    public function tradeItems(): HasMany
    {
        return $this->hasMany(TradeItem::class);
    }

    /**
     * Check if the trade request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the trade request is modified and waiting approval.
     */
    public function isModified(): bool
    {
        return $this->status === self::STATUS_MODIFIED;
    }

    /**
     * Check if the trade request is accepted.
     */
    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    /**
     * Check if the trade request is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if the trade request is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if the trade request is active (pending or modified).
     */
    public function isActive(): bool
    {
        return $this->isPending() || $this->isModified();
    }

    /**
     * Check if the trade request is finalized (accepted, rejected, or cancelled).
     */
    public function isFinalized(): bool
    {
        return $this->isAccepted() || $this->isRejected() || $this->isCancelled();
    }

    /**
     * Determine if the specified user can approve this trade.
     */
    public function canBeApprovedBy(User $user): bool
    {
        // If the trade is pending, only the receiver can approve
        if ($this->isPending()) {
            return $this->receiver_id === $user->id;
        }

        // If the trade was modified, the user who didn't modify it can approve
        if ($this->isModified()) {
            // Also check if this user has already approved it
            if ($user->id === $this->sender_id && $this->sender_approved) {
                return false;
            }

            if ($user->id === $this->receiver_id && $this->receiver_approved) {
                return false;
            }

            return $this->modified_by_id !== $user->id;
        }

        return false;
    }

    /**
     * Record approval from a specific user.
     */
    public function approveBy(User $user): bool
    {
        if (!$this->isModified()) {
            return false;
        }

        if ($user->id === $this->sender_id) {
            $this->sender_approved = true;
        } elseif ($user->id === $this->receiver_id) {
            $this->receiver_approved = true;
        } else {
            return false;
        }

        // If both have approved, the trade is ready to proceed
        if ($this->sender_approved && $this->receiver_approved) {
            $this->status = self::STATUS_PENDING; // Reset to pending for normal approval flow
        }

        $this->save();
        return true;
    }

    /**
     * Reset approval flags when a trade is modified.
     */
    public function resetApprovals(int $modifier_id): void
    {
        $this->sender_approved = ($this->sender_id === $modifier_id) ? true : null;
        $this->receiver_approved = ($this->receiver_id === $modifier_id) ? true : null;
        $this->modified_by_id = $modifier_id;
        $this->status = self::STATUS_MODIFIED;
        $this->save();
    }
}
