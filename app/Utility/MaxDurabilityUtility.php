<?php

namespace App\Utility;

use App\Models\Item;
use Illuminate\Support\Facades\Cache;

class MaxDurabilityUtility
{
    private static string $cacheKey = 'global_max_durability';

    /**
     * Get the maximum durability of all items in the database.
     *
     * @return array
     */
    public static function getMaxDurability(): array
    {
        if (!Cache::has(self::$cacheKey)) {
            $maxDurability = Item::select('durability')
                ->distinct()
                ->max('durability');
            $maxDurability = max($maxDurability, 100);
            Cache::put(self::$cacheKey, $maxDurability, now()->addMinutes(15));
        }

        return [
            'maxDurability' => Cache::get(self::$cacheKey),
            'durabilityPercentage' => (100 / Cache::get(self::$cacheKey)) * 100,
        ];
    }
}
