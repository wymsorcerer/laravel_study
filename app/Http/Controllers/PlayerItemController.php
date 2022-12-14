<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Item;
use App\Models\PlayerItem;
use App\Models\GachaData;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

define("MAX_HP", 200);
define("MAX_MP", 200);
define("MAX_ITEM_COUNT", 99);

define("STATUS_NOT_FOUND", 404);
define("STATUS_COMMON_ERROR", 400);

define("GACHA_PRICE", 10);



class PlayerItemController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		//
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \App\Http\Requests\StorePlayerItemRequest  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\Models\PlayerItem  $playerItem
	 * @return \Illuminate\Http\Response
	 */
	public function show(PlayerItem $playerItem)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\Models\PlayerItem  $playerItem
	 * @return \Illuminate\Http\Response
	 */
	public function edit(PlayerItem $playerItem)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \App\Http\Requests\UpdatePlayerItemRequest  $request
	 * @param  \App\Models\PlayerItem  $playerItem
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, PlayerItem $playerItem)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\Models\PlayerItem  $playerItem
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(PlayerItem $playerItem)
	{
		//
	}

	public function itemList($id)
	{
		return new Response(
			PlayerItem::where(['player_id' => $id])->get()
		);
	}

	public function addItem(Request $request, $id)
	{
		if (PlayerItem::where(['player_id' => $id, "item_id" => $request->item_id])->exists()) {
			// update
			$player_items = PlayerItem::where(['player_id' => $id, "item_id" => $request->item_id])->get();

			return new Response(
				PlayerItem::where(['player_id' => $id, "item_id" => $request->item_id])->update([
					"count" => $player_items[0]->count + $request->count
				])
			);
		} else {
			return new Response(
				PlayerItem::insert([
					"player_id" => $id,
					"item_id" => $request->item_id,
					"count" => $request->count
				])
			);
		}
	}

	public function useItem_Raw(Request $request, $id){

		$player = DB::select("select * from players where id = ?", [$id]);

		DB::statement("update players set mp = ? where id = ?", [$player[0]->mp + 1, $id]);

		$player = DB::select("select * from players where id = ?", [$id]);

		return new Response([
			"mp" => $player[0]->mp
		]);
	}

	public function useItem(Request $request, $id)
	{
		if (!PlayerItem::where(['player_id' => $id, "item_id" => $request->item_id])->exists()) {
			return new Response("???????????????????????????", STATUS_NOT_FOUND);
		}

		$player_items = PlayerItem::where(['player_id' => $id, "item_id" => $request->item_id])->get();
		if ($player_items[0]->count < $request->count) {
			return new Response("???????????????????????????", STATUS_COMMON_ERROR);
		}

		$player = Player::find($id);
		$item = Item::find($request->item_id);

		try {//transaction
			DB::beginTransaction();
			
			if ($player_items[0]->item_id == 1 && $player->hp < MAX_HP) { //hp??????			
				//??????????????????????????????
				$cnt = ((int)((MAX_HP - $player->hp) / $item->value)) + 1 < $request->count
					? ((int)((MAX_HP - $player->hp) / $item->value)) + 1 : $request->count;
	
				// count?????????
				PlayerItem::where(['player_id' => $id, "item_id" => $request->item_id])->update([
					"count" => $player_items[0]->count - $cnt
				]);
	
				// hp??????
				$hp = $player->hp + $item->value * $cnt > MAX_HP ? MAX_HP : $player->hp + $item->value * $cnt;
				Player::where(['id' => $id])->update(["hp" => $hp]);
			} else if ($player_items[0]->item_id == 2 && $player->mp < MAX_MP) { //mp??????
				//??????????????????????????????
				$cnt = ((int)((MAX_MP - $player->mp) / $item->value)) + 1 < $request->count
					? ((int)((MAX_MP - $player->mp) / $item->value)) + 1 : $request->count;
	
				// count?????????
				PlayerItem::where(['player_id' => $id, "item_id" => $request->item_id])->update([
					"count" => $player_items[0]->count - $cnt
				]);
	
				// mp??????
				$mp = $player->mp + $item->value * $cnt > MAX_MP ? MAX_MP : $player->mp + $item->value * $cnt;
				Player::where(['id' => $id])->update(["mp" => $mp]);
			} else {
				return new Response("HP/MP???MAX??????????????????????????????????????????????????????", STATUS_COMMON_ERROR);
			}

			DB::commit();
		} catch (\Throwable $th) {
			DB::rollBack();
		}


		// success response
		// ???????????????????????????
		$player = Player::find($id);
		$player_items = PlayerItem::where(['player_id' => $id, "item_id" => $request->item_id])->get();

		return new Response([
			"itemId" => $request->item_id,
			"count" => $player_items[0]->count,
			"player" => $player
		]);
	}

	public function purchaseItem(Request $request, $id)
	{
		//item exists?
		if (!Item::where(['id' => $request->item_id])->exists()) {
			return new Response("???????????????????????????", STATUS_NOT_FOUND);
		}

		$player_items = PlayerItem::where(['player_id' => $id, "item_id" => $request->item_id])->get();
		$player = Player::find($id);
		$item = Item::find($request->item_id);

		try {//transaction
			DB::beginTransaction();

			if ($player_items) {
				//????????????????????????
				$cnt = MAX_ITEM_COUNT - $player_items[0]->count > $request->count
					? $request->count : MAX_ITEM_COUNT - $player_items[0]->count;
	
				//????????????????????????????????????
				$cnt = (int)($player->money / $item->price) > $cnt ? $cnt : (int)($player->money / $item->price);
				if ($cnt == 0) {
					return new Response("???????????????/??????????????????????????????????????????????????????????????????????????????", STATUS_COMMON_ERROR);
				}
	
				PlayerItem::where(['player_id' => $id, "item_id" => $request->item_id])->update([
					"count" => $player_items[0]->count + $cnt
				]);
				Player::find($id)->update(["money" => $player->money - $cnt * $item->price]);
			} else {
				//????????????????????????
				$cnt = MAX_ITEM_COUNT > $request->count ? $request->count : MAX_ITEM_COUNT;
	
				//????????????????????????????????????
				$cnt = (int)($player->money / $item->price) > $request->count ? $request->count : (int)($player->money / $item->price);
				if ($cnt == 0) {
					return new Response("???????????????/??????????????????????????????????????????????????????????????????????????????", STATUS_COMMON_ERROR);
				}
	
				PlayerItem::insert([
					"player_id" => $id,
					"item_id" => $request->item_id,
					"count" => $cnt
				]);
				Player::find($id)->update(["money" => $player->money - $cnt * $item->price]);
			}

			DB::commit();
		} catch (\Throwable $th) {
			DB::rollBack();
		}

		//success response
		$player_items = PlayerItem::where(['player_id' => $id, "item_id" => $request->item_id])->get();
		$player = Player::find($id);
		return new Response([
			"itemId" => $request->item_id,
			"count" => $player_items[0]->count,
			"player" => $player
		]);
	}

	public function gachaItem(Request $request, $id)
	{
		$player = Player::find($id);
		if ($player->money < $request->count * GACHA_PRICE) {
			return new Response("??????????????????", STATUS_COMMON_ERROR);
		}

		$gacha_cnt = array();
		$request_count = $request->count;

		$gacha_data = GachaData::where(["event_id" => 1])->get(); //event 1
		while ($request_count > 0) { //count????????????
			$num = $this->generateGacha($gacha_data);
			$request_count--;

			if (isset($gacha_cnt[$num])) {
				$gacha_cnt[$num]++;
			} else {
				$gacha_cnt[$num] = 1;
			}
		}

		$total_cnt = 0;

		//transaction
		DB::transaction(function () use ($gacha_cnt, $id, $total_cnt, $player) {
			//update player_item table
			foreach ($gacha_cnt as $key => $value) {
				if ($value > 0) {
					$player_items = PlayerItem::where(['player_id' => $id, "item_id" => $key])->get();

					$n = isset($player_items[0]->count) ? $player_items[0]->count : 0;
					$cnt = MAX_ITEM_COUNT - $n  > $value ? $value : MAX_ITEM_COUNT - $n;
					$total_cnt += $cnt;

					if (isset($player_items[0]->count)) {
						PlayerItem::where(['player_id' => $id, "item_id" => $key])->update([
							"count" => $player_items[0]->count + $cnt
						]);
					} else {
						PlayerItem::insert([
							"player_id" => $id,
							"item_id" => $key,
							"count" => $cnt
						]);
					}
				}
			}
			// update player table
			Player::find($id)->update(["money" => $player->money - $total_cnt * GACHA_PRICE]);
		});

		// response
		$player = Player::find($id);
		$items = PlayerItem::select(["item_id", "count"])
			->where(['player_id' => $id])->get();

		return new Response([
			"result" => $gacha_cnt,
			"player" => [
				"money" => $player->money,
				"items" => $items
			]
		]);
	}

	private function generateGacha($gacha_data)
	{
		$result = 0;
		$rand_num = rand(1, 100); //[1,100]

		$current_percent = 0;
		foreach ($gacha_data as $value) {
			$current_percent += $value->percent;
			if ($rand_num <= $current_percent) {
				$result = $value->item_id;
				break;
			}
		}

		return $result;
	}
}
