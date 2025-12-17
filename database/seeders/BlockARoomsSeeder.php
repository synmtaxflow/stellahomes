<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Block;
use App\Models\Room;
use App\Models\Bed;
use App\Models\RoomItem;

class BlockARoomsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find Block A
        $block = Block::where('name', 'BLOCK A')->first();
        
        if (!$block) {
            $this->command->error('Block A not found!');
            return;
        }

        // Common items combinations for variety
        $itemCombinations = [
            ['Bulb', 'Table', 'Chair', 'Toilet', 'Mirror'],
            ['Bulb', 'Table', 'Chair', 'Toilet', 'Wardrobe'],
            ['Bulb', 'Table', 'Chair', 'Toilet', 'Fan'],
            ['Bulb', 'Table', 'Chair', 'Toilet', 'Window'],
            ['Bulb', 'Table', 'Chair', 'Toilet', 'Socket'],
            ['Bulb', 'Table', 'Chair', 'Toilet', 'Shelf'],
            ['Bulb', 'Table', 'Chair', 'Toilet', 'Curtain'],
            ['Bulb', 'Table', 'Chair', 'Toilet', 'Door Lock'],
            ['Bulb', 'Table', 'Chair', 'Toilet', 'Ventilation'],
            ['Bulb', 'Table', 'Chair', 'Toilet', 'Switch'],
        ];

        $this->command->info('Creating 60 rooms for Block A...');

        for ($i = 1; $i <= 60; $i++) {
            // Create room
            $room = Room::create([
                'block_id' => $block->id,
                'name' => 'Room ' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'location' => 'Floor ' . ceil($i / 12) . ', Block A',
                'has_beds' => true,
            ]);

            // Add 4 beds to each room
            for ($bedNum = 1; $bedNum <= 4; $bedNum++) {
                Bed::create([
                    'room_id' => $room->id,
                    'name' => 'Bed ' . $bedNum,
                    'rent_price' => 200000,
                    'rent_duration' => 'semester',
                    'semester_months' => 4,
                ]);
            }

            // Add items (rotate through combinations for variety)
            $items = $itemCombinations[$i % count($itemCombinations)];
            foreach ($items as $itemName) {
                RoomItem::create([
                    'room_id' => $room->id,
                    'item_name' => $itemName,
                ]);
            }

            if ($i % 10 == 0) {
                $this->command->info("Created {$i} rooms...");
            }
        }

        $this->command->info('Successfully created 60 rooms with 4 beds each and various items!');
        $this->command->info('Total: 60 rooms, 240 beds, various items per room');
    }
}
