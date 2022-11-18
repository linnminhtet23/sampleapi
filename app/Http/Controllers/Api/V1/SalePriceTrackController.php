<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SalePriceTrackResource;
use App\Models\Region;
use App\Models\Shop;
use Illuminate\Support\Facades\Auth;

class SalePriceTrackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Shop $shop, Region $region)
    {
        $user = Auth::user();
        $shops = $user->shops;

        $sale_price_tracks = [];

        foreach ($shops as $single_shop) {
            if ($single_shop->id == $shop->id) {
                foreach ($shop->salePriceTracks as $sale_price_track) {
                    if ($sale_price_track->region_id == $region->id) {
                        array_push($sale_price_tracks, $sale_price_track);
                    }
                }
            }
        }
        return response()->json(["status" => "success", "data" => SalePriceTrackResource::collection($sale_price_tracks), "total" => count($sale_price_tracks)]);
    }
}
