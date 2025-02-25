<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\TradeRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class TradeRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sender_id' => User::factory(),
            'receiver_id' => User::factory(),
            'status' => TradeRequest::STATUS_PENDING,
        ];
    }

    public function accepted(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => TradeRequest::STATUS_ACCEPTED,
            ];
        });
    }

    public function rejected(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => TradeRequest::STATUS_REJECTED,
            ];
        });
    }
}
