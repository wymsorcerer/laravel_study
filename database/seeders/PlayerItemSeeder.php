<?php

namespace Database\Seeders;

use App\Models\PlayerItem;
use Illuminate\Database\Seeder;

class PlayerItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$testData = [
            ['player_id' => 1, 'item_id' => 1, 'count' => 10],
            ['player_id' => 1, 'item_id' => 2, 'count' => 10],
			['player_id' => 2, 'item_id' => 1, 'count' => 10],
            ['player_id' => 2, 'item_id' => 2, 'count' => 10]
        ];

		foreach ($testData as $datum) {
			$player_item = new PlayerItem;
			$player_item->player_id = $datum['player_id'];
			$player_item->item_id = $datum['item_id'];
			$player_item->count = $datum['count'];
			$player_item->save();
        }
    }
}
