<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'type',
        'rarity',
        'strength',
        'speed',
        'durability',
        'magic_properties',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'strength' => 'integer',
        'speed' => 'integer',
        'durability' => 'integer',
        'magic_properties' => 'array',
    ];

    /**
     * Get the inventories for this item.
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }
}
