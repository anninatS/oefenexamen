<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement([
            Notification::TYPE_TRADE_REQUEST,
            Notification::TYPE_TRADE_ACCEPTED,
            Notification::TYPE_TRADE_REJECTED,
            Notification::TYPE_TRADE_UPDATED,
            Notification::TYPE_ITEM_RECEIVED,
            Notification::TYPE_SYSTEM,
        ]);

        $message = match($type) {
            Notification::TYPE_TRADE_REQUEST => 'You have received a new trade request from ' . fake()->name(),
            Notification::TYPE_TRADE_ACCEPTED => 'Your trade request has been accepted by ' . fake()->name(),
            Notification::TYPE_TRADE_REJECTED => 'Your trade request has been rejected by ' . fake()->name(),
            Notification::TYPE_TRADE_UPDATED => fake()->randomElement([
                fake()->name() . ' has added items to your trade request. Please review and approve the changes.',
                fake()->name() . ' has removed items from your trade request. Please review and approve the changes.',
                fake()->name() . ' has modified your trade request. Please review and approve the changes.'
            ]),
            Notification::TYPE_ITEM_RECEIVED => 'You have received a new item: ' . fake()->word(),
            Notification::TYPE_SYSTEM => fake()->sentence(),
        };

        return [
            'user_id' => User::factory(),
            'type' => $type,
            'message' => $message,
            'read' => fake()->boolean(30), // Most notifications are unread
        ];
    }

    public function system(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Notification::TYPE_SYSTEM,
                'message' => fake()->sentence(),
            ];
        });
    }
}
