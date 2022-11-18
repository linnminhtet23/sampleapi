<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PriceRequest;
use App\Http\Resources\PriceResource;
use App\Imports\PriceImport;
use App\Models\Price;
use App\Models\Region;
use App\Models\SalePriceTrack;
use App\Models\Shop;
use App\Utils\ErrorType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class PriceController extends Controller
{
    const ITEM_ID = 'item_id';
    const REGION_ID = 'region_id';
    const SALE_PRICE = 'sale_price';
    const SHOP_ID = 'shop_id';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Shop $shop, Region $region)
    {
        $user = Auth::user();
        $shops = $user->shops;

        $prices = [];

        foreach ($shops as $single_shop) {
            if ($single_shop->id == $shop->id) {
                foreach ($shop->prices as $price) {
                    if ($price->region_id == $region->id) {
                        array_push($prices, $price);
                    }
                }
            }
        }

        return response()->json(["status" => "success", "data" => PriceResource::collection($prices), "total" => count($prices)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PriceRequest $request)
    {
        try {
            $shop_id = $request->get(self::SHOP_ID);

            $user = Auth::user();
            $shops = $user->shops;

            foreach ($shops as $singleShop) {
                if ($singleShop->id == $shop_id) {
                    $item_id = trim($request->get(self::ITEM_ID));
                    $region_id = trim($request->get(self::REGION_ID));
                    $sale_price = trim($request->get(self::SALE_PRICE));

                    $price = new Price();
                    $price->item_id = $item_id;
                    $price->region_id = $region_id;
                    $price->sale_price = $sale_price;
                    $price->shop_id = $shop_id;

                    $price->save();

                    $sale_price_track = new SalePriceTrack();
                    $sale_price_track->item_id = $item_id;
                    $sale_price_track->region_id = $region_id;
                    $sale_price_track->sale_price = $sale_price;
                    $sale_price_track->shop_id = $shop_id;

                    $sale_price_track->save();

                    return jsend_success(new PriceResource($price), JsonResponse::HTTP_CREATED);
                }
            }
            return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (Exception $ex) {
            Log::error(__('api.saved-failed', ['model' => class_basename(Price::class)]), [
                'code' => $ex->getCode(),
                'trace' => $ex->getTrace(),
            ]);

            return jsend_error(__('api.saved-failed', ['model' => class_basename(Price::class)]), [
                $ex->getCode(),
                ErrorType::SAVE_ERROR,
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Price $price)
    {
        $user = Auth::user();
        $shops = $user->shops;

        foreach ($shops as $shop) {
            if ($shop->id == $price->shop_id) {
                return jsend_success(new PriceResource($price));
            }
        }
        return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PriceRequest $request, Price $price)
    {
        try {
            $shop_id = $request->get(self::SHOP_ID);

            $user = Auth::user();
            $shops = $user->shops;

            foreach ($shops as $singleShop) {
                if ($singleShop->id == $shop_id) {
                    $item_id = trim($request->get(self::ITEM_ID));
                    $region_id = trim($request->get(self::REGION_ID));
                    $sale_price = trim($request->get(self::SALE_PRICE));

                    $isPriceTrack = false;

                    if ($price->sale_price != $sale_price) {
                        $isPriceTrack = true;
                    }

                    $price->item_id = $item_id;
                    $price->region_id = $region_id;
                    $price->sale_price = $sale_price;
                    $price->shop_id = $shop_id;

                    $price->save();

                    if ($isPriceTrack) {
                        $sale_price_track = new SalePriceTrack();
                        $sale_price_track->item_id = $item_id;
                        $sale_price_track->region_id = $region_id;
                        $sale_price_track->sale_price = $sale_price;
                        $sale_price_track->shop_id = $shop_id;

                        $sale_price_track->save();
                    }

                    return jsend_success(new PriceResource($price), JsonResponse::HTTP_CREATED);
                }
            }
            return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (Exception $ex) {
            Log::error(__('api.updated-failed', ['model' => class_basename(Price::class)]), [
                'code' => $ex->getCode(),
                'trace' => $ex->getTrace(),
            ]);

            return jsend_error(__('api.updated-failed', ['model' => class_basename(Price::class)]), [
                $ex->getCode(),
                ErrorType::UPDATE_ERROR,
            ]);
        }
    }

    public function import()
    {
        Excel::import(new PriceImport, request()->file('file'));

        return jsend_success(['message' => 'Successfully imported!'], JsonResponse::HTTP_ACCEPTED);
    }
}
