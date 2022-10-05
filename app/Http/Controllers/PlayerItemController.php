<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Item;
use App\Models\PlayerItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

define("MAX_HP", 200);
define("MAX_MP", 200);
define("MAX_ITEM_COUNT", 99);

define("STATUS_NOT_FOUND", 404);
define("STATUS_COMMON_ERROR", 400);



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

	public function useItem(Request $request, $id)
	{
		if (!PlayerItem::where(['player_id' => $id, "item_id" => $request->item_id])->exists()) {
			return new Response("アイテム存在しない", STATUS_NOT_FOUND);
		}
		
		$player_items = PlayerItem::where(['player_id' => $id, "item_id" => $request->item_id])->get();
		if ($player_items[0]->count < $request->count) {
			return new Response("アイテム数足りない", STATUS_COMMON_ERROR);
		}

		$player = Player::find($id);

		if ($player_items[0]->item_id == 1 && $player->hp < MAX_HP) { //hp回復
			$item = Item::find($request->item_id);
			//上限まで使う数を計算
			$cnt = ((int)((MAX_HP - $player->hp) / $item->value)) + 1 < $request->count
				? ((int)((MAX_HP - $player->hp) / $item->value)) + 1 : $request->count;

			// count数更新
			PlayerItem::where(['player_id' => $id, "item_id" => $request->item_id])->update([
				"count" => $player_items[0]->count - $cnt
			]);

			// hp更新
			$hp = $player->hp + $item->value * $cnt > MAX_HP ? MAX_HP : $player->hp + $item->value * $cnt;
			Player::where(['id' => $id])->update(["hp" => $hp]);
		} else if ($player_items[0]->item_id == 2 && $player->mp < MAX_MP) { //mp回復
			$item = Item::find($request->item_id);
			//上限まで使う数を計算
			$cnt = ((int)((MAX_MP - $player->mp) / $item->value)) + 1 < $request->count
				? ((int)((MAX_MP - $player->mp) / $item->value)) + 1 : $request->count;

			// count数更新
			PlayerItem::where(['player_id' => $id, "item_id" => $request->item_id])->update([
				"count" => $player_items[0]->count - $cnt
			]);

			// mp更新
			$mp = $player->mp + $item->value * $cnt > MAX_MP ? MAX_MP : $player->mp + $item->value * $cnt;
			Player::where(['id' => $id])->update(["mp" => $mp]);
		} else {
			return new Response("HP/MPがMAXだったため、アイテムを使用しなかった", STATUS_COMMON_ERROR);
		}

		// success response
		// 最新のデータを取得
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
			return new Response("アイテム存在しない", STATUS_NOT_FOUND);
		}

		$player_items = PlayerItem::where(['player_id' => $id, "item_id" => $request->item_id])->get();
		$player = Player::find($id);
		$item = Item::find($request->item_id);

		if ($player_items) {
			//上限まで買える数
			$cnt = MAX_ITEM_COUNT - $player_items[0]->count > $request->count
				? $request->count : MAX_ITEM_COUNT - $player_items[0]->count;

			//お金なくなるまで買える数
			$cnt = (int)($player->money / $item->price) > $cnt ? $cnt : (int)($player->money / $item->price);
			if ($cnt == 0) {
				return new Response("お金がない/アイテム数上限になったため、アイテムを購入しなかった", STATUS_COMMON_ERROR);
			}

			PlayerItem::where(['player_id' => $id, "item_id" => $request->item_id])->update([
				"count" => $player_items[0]->count + $cnt
			]);
			Player::find($id)->update(["money" => $player->money - $cnt * $item->price]);
		} else {
			//上限まで買える数
			$cnt = MAX_ITEM_COUNT > $request->count ? $request->count : MAX_ITEM_COUNT;

			//お金なくなるまで買える数
			$cnt = (int)($player->money / $item->price) > $request->count ? $request->count : (int)($player->money / $item->price);
			if ($cnt == 0) {
				return new Response("お金がない/アイテム数上限になったため、アイテムを購入しなかった", STATUS_COMMON_ERROR);
			}

			PlayerItem::insert([
				"player_id" => $id,
				"item_id" => $request->item_id,
				"count" => $cnt
			]);
			Player::find($id)->update(["money" => $player->money - $cnt * $item->price]);
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
}
