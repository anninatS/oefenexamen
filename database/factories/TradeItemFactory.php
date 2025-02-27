<?php

namespace Database\Factories;

use App\Models\TradeRequest;
use App\Models\Inventory;
use App\Models\TradeItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class TradeItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'trade_request_id' => TradeRequest::factory(),
            'inventory_id' => Inventory::factory(),
            'direction' => fake()->randomElement([
                TradeItem::DIRECTION_OFFER,
                TradeItem::DIRECTION_REQUEST,
            ]),
        ];
    }

    /**
     * Configure the factory to create an offered item (from sender).
     */
    public function offer(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'direction' => TradeItem::DIRECTION_OFFER,
            ];
        });
    }

    /**
     * Configure the factory to create a requested item (from receiver).
     */
    public function request(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'direction' => TradeItem::DIRECTION_REQUEST,
            ];
        });
    }
}
