<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Item;
use App\Models\Inventory;
use App\Models\TradeRequest;
use App\Models\TradeItem;
use App\Models\Notification;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'stan',
            'email' => 'stan@stan.stan',
        ]);

        $testUser = User::factory()->create([
            'name' => 'user',
            'email' => 'user@user.user',
        ]);

        $users = User::factory()->count(10)->create();
        $allUsers = $users->concat([$admin, $testUser]);

        $commonItems = Item::factory()->count(25)->create(['rarity' => 'common']);
        $uncommonItems = Item::factory()->count(20)->create(['rarity' => 'uncommon']);
        $rareItems = Item::factory()->count(15)->create(['rarity' => 'rare']);
        $epicItems = Item::factory()->count(10)->create(['rarity' => 'epic']);

        $weapons = Item::factory()->weapon()->count(10)->create();
        $armor = Item::factory()->armor()->count(10)->create();
        $legendaryItems = Item::factory()->legendary()->count(5)->create();

        $allItems = $commonItems->concat($uncommonItems)
            ->concat($rareItems)
            ->concat($epicItems)
            ->concat($weapons)
            ->concat($armor)
            ->concat($legendaryItems);

        // Give our players some random stuff to start with
        foreach ($allUsers as $user) {
            // Each player gets 5-10 random items
            $itemCount = fake()->numberBetween(5, 10);
            $userItems = $allItems->random($itemCount);

            foreach ($userItems as $item) {
                $time = fake()->numberBetween(time() - 30 * 24 * 60 * 60, time()); // Items acquired in the last 30 days

                Inventory::factory()->create([
                    'user_id' => $user->id,
                    'item_id' => $item->id,
                    'acquired_at' => $time,
                ]);
            }
        }

        // Some trading activity between players
        $tradeRequestCount = 20;
        $userInventories = [];

        // Keep track of who has what
        foreach ($allUsers as $user) {
            $userInventories[$user->id] = Inventory::where('user_id', $user->id)->get();
        }

        for ($i = 0; $i < $tradeRequestCount; $i++) {
            $sender = $allUsers->random();
            $receiver = $allUsers->filter(fn($u) => $u->id !== $sender->id)->random();

            if (!isset($userInventories[$sender->id]) || $userInventories[$sender->id]->count() < 4) {
                continue;
            }

            $status = fake()->randomElement([
                TradeRequest::STATUS_PENDING,
                TradeRequest::STATUS_PENDING,  // Weighted to be more common
                TradeRequest::STATUS_ACCEPTED,
                TradeRequest::STATUS_REJECTED
            ]);

            $tradeRequest = TradeRequest::factory()->create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'status' => $status,
            ]);

            $senderInventory = $userInventories[$sender->id];

            // Max 2 items to trade
            $maxItemsToTrade = min(2, $senderInventory->count() - 2);
            $itemCount = max(1, min($maxItemsToTrade, fake()->numberBetween(1, 2)));
            $itemsToTrade = $senderInventory->random($itemCount);

            $tradedInventoryIds = [];
            foreach ($itemsToTrade as $inventoryItem) {
                TradeItem::factory()->create([
                    'trade_request_id' => $tradeRequest->id,
                    'inventory_id' => $inventoryItem->id
                ]);
                $tradedInventoryIds[] = $inventoryItem->id;
            }

            // If the trade was accepted, move the items to their new owner
            if ($status === TradeRequest::STATUS_ACCEPTED) {
                foreach ($itemsToTrade as $inventoryItem) {
                    $inventoryItem->update([
                        'user_id' => $receiver->id,
                        'acquired_at' => time(),
                    ]);

                    Notification::factory()->create([
                        'user_id' => $receiver->id,
                        'type' => Notification::TYPE_ITEM_RECEIVED,
                        'message' => "You received {$inventoryItem->item->name} in a trade with {$sender->name}",
                        'read' => fake()->boolean(30), // Most notifications start unread
                    ]);
                }

                // Update our tracking so we don't try to trade these items again
                // 1. Remove items from sender's inventory
                $userInventories[$sender->id] = $senderInventory->filter(
                    fn($item) => !in_array($item->id, $tradedInventoryIds)
                );

                // 2. Add items to receiver's inventory
                if (!isset($userInventories[$receiver->id])) {
                    $userInventories[$receiver->id] = collect();
                }

                foreach ($itemsToTrade as $item) {
                    $userInventories[$receiver->id]->push($item);
                }

                Notification::factory()->create([
                    'user_id' => $sender->id,
                    'type' => Notification::TYPE_TRADE_ACCEPTED,
                    'message' => "{$receiver->name} has accepted your trade request",
                    'read' => fake()->boolean(50),
                ]);
            } elseif ($status === TradeRequest::STATUS_PENDING) {
                Notification::factory()->create([
                    'user_id' => $receiver->id,
                    'type' => Notification::TYPE_TRADE_REQUEST,
                    'message' => "{$sender->name} has sent you a trade request",
                    'read' => false, // New trade requests always start unread
                ]);
            } elseif ($status === TradeRequest::STATUS_REJECTED) {
                Notification::factory()->create([
                    'user_id' => $sender->id,
                    'type' => Notification::TYPE_TRADE_REJECTED,
                    'message' => "{$receiver->name} has rejected your trade request",
                    'read' => fake()->boolean(50),
                ]);
            }
        }

        foreach ($allUsers as $user) {
            $notificationCount = fake()->numberBetween(1, 5);
            Notification::factory()->system()->count($notificationCount)->create([
                'user_id' => $user->id,
            ]);
        }

        foreach ($allUsers as $user) {
            // Find items they got in the last week
            $recentItems = Inventory::where('user_id', $user->id)
                ->where('acquired_at', '>', time() - 7 * 24 * 60 * 60)
                ->with('item')
                ->get();

            foreach ($recentItems as $inventory) {
                // Don't notify for every item - that would be spammy
                if (fake()->boolean(30)) {
                    Notification::factory()->create([
                        'user_id' => $user->id,
                        'type' => Notification::TYPE_ITEM_RECEIVED,
                        'message' => "You have received a new item: {$inventory->item->name}",
                        'read' => fake()->boolean(50),
                    ]);
                }
            }
        }
    }
}
