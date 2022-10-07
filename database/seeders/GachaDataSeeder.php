<?php

namespace Database\Seeders;

use App\Models\GachaData;
use Illuminate\Database\Seeder;

class GachaDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$testData = [
            ['event_id' => 1, 'item_id' => 1, 'percent' => 60],
            ['event_id' => 1, 'item_id' => 2, 'percent' => 40],
        ];

		foreach ($testData as $datum) {
			$gacha_data = new GachaData();
			$gacha_data->event_id = $datum['event_id'];
			$gacha_data->item_id = $datum['item_id'];
			$gacha_data->percent = $datum['percent'];
			$gacha_data->save();
        }
    }
}
