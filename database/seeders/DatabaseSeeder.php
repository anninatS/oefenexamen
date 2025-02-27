<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Item;
use App\Models\Inventory;
use App\Models\TradeRequest;
use App\Models\TradeItem;
use App\Models\Notification;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'stan',
            'email' => 'stan@stan.stan',
            'password' => Hash::make('stan'),
        ]);

        $testUser = User::factory()->create([
            'name' => 'user',
            'email' => 'user@user.user',
            'password' => Hash::make('user'),
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
        $tradeRequestCount = 25; // Increased count to include more variety
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

            if (!isset($userInventories[$receiver->id]) || $userInventories[$receiver->id]->count() < 4) {
                continue;
            }

            // Decide on trade status, now including MODIFIED status
            $status = fake()->randomElement([
                TradeRequest::STATUS_PENDING,
                TradeRequest::STATUS_PENDING,  // Weighted to be more common
                TradeRequest::STATUS_MODIFIED, // Add modified status
                TradeRequest::STATUS_ACCEPTED,
                TradeRequest::STATUS_REJECTED
            ]);

            // For modified status, randomly pick who modified it
            $modifiedById = null;
            if ($status === TradeRequest::STATUS_MODIFIED) {
                $modifiedById = fake()->boolean() ? $sender->id : $receiver->id;
            }

            $tradeRequest = TradeRequest::factory()->create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'status' => $status,
                'modified_by_id' => $modifiedById,
            ]);

            $senderInventory = $userInventories[$sender->id];
            $receiverInventory = $userInventories[$receiver->id];

            // Max 2 sender items to trade
            $senderMaxItems = min(2, $senderInventory->count() - 2);
            $senderItemCount = max(1, min($senderMaxItems, fake()->numberBetween(1, 2)));
            $senderItemsToTrade = $senderInventory->random($senderItemCount);

            // Create a bi-directional trade by also having the receiver offer items
            $receiverMaxItems = min(2, $receiverInventory->count() - 2);
            // Sometimes have no receiver items (traditional one-way trade)
            $includeBidirectional = fake()->boolean(70); // 70% chance of bi-directional trade
            $receiverItemCount = $includeBidirectional ?
                max(1, min($receiverMaxItems, fake()->numberBetween(1, 2))) : 0;

            $receiverItemsToTrade = $receiverItemCount > 0 ?
                $receiverInventory->random($receiverItemCount) : collect([]);

            $tradedSenderInventoryIds = [];
            $tradedReceiverInventoryIds = [];

            // Add sender's items to the trade (direction: offer)
            foreach ($senderItemsToTrade as $inventoryItem) {
                TradeItem::factory()->create([
                    'trade_request_id' => $tradeRequest->id,
                    'inventory_id' => $inventoryItem->id,
                    'direction' => TradeItem::DIRECTION_OFFER
                ]);
                $tradedSenderInventoryIds[] = $inventoryItem->id;
            }

            // Add receiver's items to the trade (direction: request)
            foreach ($receiverItemsToTrade as $inventoryItem) {
                TradeItem::factory()->create([
                    'trade_request_id' => $tradeRequest->id,
                    'inventory_id' => $inventoryItem->id,
                    'direction' => TradeItem::DIRECTION_REQUEST
                ]);
                $tradedReceiverInventoryIds[] = $inventoryItem->id;
            }

            // Create trade notifications based on status
            if ($status === TradeRequest::STATUS_PENDING) {
                // New trade request notification
                Notification::factory()->create([
                    'user_id' => $receiver->id,
                    'type' => Notification::TYPE_TRADE_REQUEST,
                    'message' => "{$sender->name} has sent you a trade request",
                    'read' => fake()->boolean(30), // 30% chance of being read
                ]);
            }
            elseif ($status === TradeRequest::STATUS_MODIFIED) {
                // Modified trade notification
                $notificationRecipientId = ($modifiedById === $sender->id) ? $receiver->id : $sender->id;
                $modifierName = ($modifiedById === $sender->id) ? $sender->name : $receiver->name;

                Notification::factory()->create([
                    'user_id' => $notificationRecipientId,
                    'type' => Notification::TYPE_TRADE_UPDATED,
                    'message' => "{$modifierName} has modified the trade request. Please review and approve the changes.",
                    'read' => fake()->boolean(30), // 30% chance of being read
                ]);
            }
            elseif ($status === TradeRequest::STATUS_ACCEPTED) {
                // If the trade was accepted, move the items to their new owners
                // Transfer sender's items to receiver
                foreach ($senderItemsToTrade as $inventoryItem) {
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

                // Transfer receiver's items to sender
                foreach ($receiverItemsToTrade as $inventoryItem) {
                    $inventoryItem->update([
                        'user_id' => $sender->id,
                        'acquired_at' => time(),
                    ]);

                    Notification::factory()->create([
                        'user_id' => $sender->id,
                        'type' => Notification::TYPE_ITEM_RECEIVED,
                        'message' => "You received {$inventoryItem->item->name} in a trade with {$receiver->name}",
                        'read' => fake()->boolean(30), // Most notifications start unread
                    ]);
                }

                // Update our tracking so we don't try to trade these items again
                // 1. Remove items from sender's inventory
                $userInventories[$sender->id] = $senderInventory->filter(
                    fn($item) => !in_array($item->id, $tradedSenderInventoryIds)
                );

                // 2. Add receiver's items to sender's inventory
                foreach ($receiverItemsToTrade as $item) {
                    $userInventories[$sender->id]->push($item);
                }

                // 3. Remove items from receiver's inventory
                $userInventories[$receiver->id] = $receiverInventory->filter(
                    fn($item) => !in_array($item->id, $tradedReceiverInventoryIds)
                );

                // 4. Add sender's items to receiver's inventory
                foreach ($senderItemsToTrade as $item) {
                    $userInventories[$receiver->id]->push($item);
                }

                Notification::factory()->create([
                    'user_id' => $sender->id,
                    'type' => Notification::TYPE_TRADE_ACCEPTED,
                    'message' => "{$receiver->name} has accepted your trade request",
                    'read' => fake()->boolean(50),
                ]);
            }
            elseif ($status === TradeRequest::STATUS_REJECTED) {
                // Rejection notification
                Notification::factory()->create([
                    'user_id' => $sender->id,
                    'type' => Notification::TYPE_TRADE_REJECTED,
                    'message' => "{$receiver->name} has rejected your trade request",
                    'read' => fake()->boolean(50),
                ]);
            }
        }

        // Create a few special test cases for trades in modified state
        // Use our test user for easy access
        $specialTradesCount = 3;

        for ($i = 0; $i < $specialTradesCount; $i++) {
            $partner = $users->random();

            // Create trades where testUser is the sender and ones where they're the receiver
            $sender = fake()->boolean() ? $testUser : $partner;
            $receiver = ($sender->id === $testUser->id) ? $partner : $testUser;

            // Get inventories
            $senderInventory = Inventory::where('user_id', $sender->id)->get();
            $receiverInventory = Inventory::where('user_id', $receiver->id)->get();

            // Skip if not enough inventory items
            if ($senderInventory->count() < 2 || $receiverInventory->count() < 2) {
                continue;
            }

            // Prepare some items
            $senderItems = $senderInventory->random(2);
            $receiverItems = $receiverInventory->random(1);

            // Create a trade in MODIFIED state
            $modified = fake()->boolean(); // Who modified it?
            $modifier = $modified ? $sender : $receiver;

            $trade = TradeRequest::factory()->create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'status' => TradeRequest::STATUS_MODIFIED,
                'modified_by_id' => $modifier->id,
            ]);

            // Add items from both sides
            foreach ($senderItems as $item) {
                TradeItem::factory()->create([
                    'trade_request_id' => $trade->id,
                    'inventory_id' => $item->id,
                    'direction' => TradeItem::DIRECTION_OFFER,
                ]);
            }

            foreach ($receiverItems as $item) {
                TradeItem::factory()->create([
                    'trade_request_id' => $trade->id,
                    'inventory_id' => $item->id,
                    'direction' => TradeItem::DIRECTION_REQUEST,
                ]);
            }

            // Create notification for the modified trade
            $notificationRecipient = ($modifier->id === $sender->id) ? $receiver : $sender;

            Notification::factory()->create([
                'user_id' => $notificationRecipient->id,
                'type' => Notification::TYPE_TRADE_UPDATED,
                'message' => "{$modifier->name} has modified the trade request. Please review and approve the changes.",
                'read' => false, // Unread for testing purposes
            ]);
        }

        // Create some system notifications
        foreach ($allUsers as $user) {
            $notificationCount = fake()->numberBetween(1, 5);
            Notification::factory()->system()->count($notificationCount)->create([
                'user_id' => $user->id,
            ]);
        }

        // Create some recent item acquisition notifications
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
