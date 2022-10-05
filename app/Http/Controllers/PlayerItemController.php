<?php

namespace App\Http\Controllers;

use App\Models\PlayerItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
}
