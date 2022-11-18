<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuyPriceTrackResource;
use App\Models\Region;
use App\Models\Shop;
use Illuminate\Support\Facades\Auth;

class BuyPriceTrackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Shop $shop)
    {
        $user = Auth::user();
        $shops = $user->shops;

        $buy_price_tracks = [];
        
        if ($shop->buyPriceTracks->isEmpty() && $shop->id == null) {
            foreach ($shops as $single_shop) {
                foreach ($single_shop->buyPriceTracks as $buy_price_track) {
                    array_push($buy_price_tracks, $buy_price_track);
                }
            }
        } else {
            foreach ($shops as $single_shop) {
                if ($single_shop->id == $shop->id) {
                    foreach ($shop->buyPriceTracks as $buy_price_track) {
                        array_push($buy_price_tracks, $buy_price_track);
                    }
                    break;
                }
            }
        }

        return response()->json(["status" => "success", "data" => BuyPriceTrackResource::collection($buy_price_tracks), "total" => count($buy_price_tracks)]);
    }
}
