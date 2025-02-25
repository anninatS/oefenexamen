<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    public function definition(): array
    {
        $types = ['weapon', 'armor', 'potion', 'artifact', 'accessory', 'scroll'];
        $rarities = ['common', 'uncommon', 'rare', 'epic', 'legendary', 'mythic'];

        $type = fake()->randomElement($types);

        $name = match($type) {
            'weapon' => fake()->randomElement(['Sword', 'Axe', 'Bow', 'Staff', 'Dagger', 'Mace', 'Wand']) . ' of ' . fake()->word(),
            'armor' => fake()->randomElement(['Helmet', 'Chestplate', 'Gauntlets', 'Boots', 'Shield', 'Cloak']) . ' of ' . fake()->word(),
            'potion' => 'Potion of ' . fake()->randomElement(['Healing', 'Strength', 'Invisibility', 'Night Vision', 'Speed', 'Endurance']),
            'artifact' => fake()->randomElement(['Amulet', 'Ring', 'Crown', 'Orb', 'Relic']) . ' of ' . fake()->word(),
            'accessory' => fake()->randomElement(['Belt', 'Bracelet', 'Necklace', 'Earring', 'Charm']) . ' of ' . fake()->word(),
            'scroll' => 'Scroll of ' . fake()->randomElement(['Fireball', 'Teleportation', 'Summoning', 'Wisdom', 'Protection', 'Binding']),
        };

        $rarity = fake()->randomElement($rarities);
        $propertiesCount = match($rarity) {
            'common' => fake()->numberBetween(0, 1),
            'uncommon' => fake()->numberBetween(1, 2),
            'rare' => fake()->numberBetween(2, 3),
            'epic' => fake()->numberBetween(3, 4),
            'legendary' => fake()->numberBetween(4, 5),
            'mythic' => fake()->numberBetween(5, 6),
        };

        $possibleProperties = [
            'fire_resistance', 'ice_resistance', 'lightning_resistance',
            'poison_resistance', 'critical_hit', 'life_steal',
            'mana_regeneration', 'cooldown_reduction', 'area_damage',
            'extra_gold', 'experience_boost', 'health_regeneration'
        ];

        $properties = [];
        $selectedProperties = fake()->randomElements($possibleProperties, $propertiesCount);

        foreach ($selectedProperties as $property) {
            $properties[$property] = fake()->numberBetween(5, 25);
        }

        $rarityMultiplier = match($rarity) {
            'common' => 1,
            'uncommon' => 1.5,
            'rare' => 2,
            'epic' => 2.5,
            'legendary' => 3,
            'mythic' => 4,
        };

        return [
            'name' => $name,
            'description' => fake()->sentence(fake()->numberBetween(10, 15)),
            'type' => $type,
            'rarity' => $rarity,
            'strength' => (int) (fake()->numberBetween(5, 20) * $rarityMultiplier),
            'speed' => (int) (fake()->numberBetween(5, 20) * $rarityMultiplier),
            'durability' => (int) (fake()->numberBetween(50, 100) * $rarityMultiplier),
            'magic_properties' => json_encode($properties),
        ];
    }

    public function weapon(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'weapon',
                'name' => fake()->randomElement(['Sword', 'Axe', 'Bow', 'Staff', 'Dagger', 'Mace', 'Wand']) . ' of ' . fake()->word(),
                'strength' => fake()->numberBetween(15, 30),
                'speed' => fake()->numberBetween(5, 15),
            ];
        });
    }

    public function armor(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'armor',
                'name' => fake()->randomElement(['Helmet', 'Chestplate', 'Gauntlets', 'Boots', 'Shield', 'Cloak']) . ' of ' . fake()->word(),
                'durability' => fake()->numberBetween(80, 150),
                'strength' => fake()->numberBetween(5, 15),
            ];
        });
    }

    public function legendary(): static
    {
        return $this->state(function (array $attributes) {
            $properties = [];
            $selectedProperties = fake()->randomElements([
                'fire_resistance', 'ice_resistance', 'lightning_resistance',
                'poison_resistance', 'critical_hit', 'life_steal',
                'mana_regeneration', 'cooldown_reduction', 'area_damage'
            ], 5);

            foreach ($selectedProperties as $property) {
                $properties[$property] = fake()->numberBetween(15, 30);
            }

            return [
                'rarity' => 'legendary',
                'strength' => fake()->numberBetween(30, 50),
                'speed' => fake()->numberBetween(20, 40),
                'durability' => fake()->numberBetween(150, 300),
                'magic_properties' => json_encode($properties),
            ];
        });
    }
}
