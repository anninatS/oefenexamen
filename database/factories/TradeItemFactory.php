<?php

namespace Database\Factories;

use App\Models\TradeRequest;
use App\Models\Inventory;
use Illuminate\Database\Eloquent\Factories\Factory;

class TradeItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'trade_request_id' => TradeRequest::factory(),
            'inventory_id' => Inventory::factory(),
        ];
    }
}
