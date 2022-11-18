<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemRequest;
use App\Http\Resources\ItemResource;
use App\Imports\ItemImport;
use App\Models\BuyPriceTrack;
use App\Models\Item;
use App\Models\Shop;
use App\Utils\ErrorType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ItemController extends Controller
{
    const CODE = 'code';
    const NAME = 'name';
    const LEFT_ITEM = 'left_item';
    const BUY_PRICE = 'buy_price';
    const CATEGORY_ID = 'category_id';
    const SHOP_ID = 'shop_id';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Shop $shop)
    {
        $user = Auth::user();
        $shops = $user->shops;

        $items = [];

        if ($shop->items->isEmpty() && $shop->id == null) {
            foreach ($shops as $single_shop) {
                foreach ($single_shop->items as $item) {
                    array_push($items, $item);
                }
            }
        } else {
            foreach ($shops as $single_shop) {
                if ($single_shop->id == $shop->id) {
                    foreach ($shop->items as $item) {
                        array_push($items, $item);
                    }
                    break;
                }
            }
        }

        return response()->json(["status" => "success", "data" => ItemResource::collection($items), "total" => count($items)]);
    }
    
    public function lowItems(Shop $shop)
    {
        $user = Auth::user();
        $shops = $user->shops;

        $items = [];

        if ($shop->items->where('left_item', '<', 200)->isEmpty() && $shop->id == null) {
            foreach ($shops as $single_shop) {
                foreach ($single_shop->items->where('left_item', '<', 200) as $item) {
                    array_push($items, $item);
                }
            }
        } else {
            foreach ($shops as $single_shop) {
                if ($single_shop->id == $shop->id) {
                    foreach ($shop->items->where('left_item', '<', 200) as $item) {
                        array_push($items, $item);
                    }
                    break;
                }
            }
        }

        return response()->json(["status" => "success", "data" => ItemResource::collection($items), "total" => count($items)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ItemRequest $request)
    {
        try {
            $shop_id = $request->get(self::SHOP_ID);

            $user = Auth::user();
            $shops = $user->shops;

            foreach ($shops as $singleShop) {
                if ($singleShop->id == $shop_id) {
                    $code = trim($request->get(self::CODE));
                    $name = trim($request->get(self::NAME));
                    $left_item = trim($request->get(self::LEFT_ITEM));
                    $buy_price = trim($request->get(self::BUY_PRICE));
                    $category_id = trim($request->get(self::CATEGORY_ID));

                    $item = new Item();
                    $item->code = $code;
                    $item->name = $name;
                    $item->left_item = $left_item;
                    $item->buy_price = $buy_price;
                    $item->category_id = $category_id;
                    $item->shop_id = $shop_id;

                    $item->save();

                    $buy_price_track = new BuyPriceTrack();
                    $buy_price_track->item_id = $item->id;
                    $buy_price_track->buy_price = $buy_price;
                    $buy_price_track->shop_id = $shop_id;

                    $buy_price_track->save();

                    return jsend_success(new ItemResource($item), JsonResponse::HTTP_CREATED);
                }
            }
            return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (Exception $ex) {
            Log::error(__('api.saved-failed', ['model' => class_basename(Item::class)]), [
                'code' => $ex->getCode(),
                'trace' => $ex->getTrace(),
            ]);

            return jsend_error(__('api.saved-failed', ['model' => class_basename(Item::class)]), [
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
    public function show(Item $item)
    {
        $user = Auth::user();
        $shops = $user->shops;

        foreach ($shops as $shop) {
            if ($shop->id == $item->shop_id) {
                return jsend_success(new ItemResource($item));
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
    public function update(ItemRequest $request, Item $item)
    {
        try {
            $shop_id = $request->get(self::SHOP_ID);

            $user = Auth::user();
            $shops = $user->shops;


            foreach ($shops as $singleShop) {
                if ($singleShop->id == $shop_id) {
                    $code = trim($request->get(self::CODE));
                    $name = trim($request->get(self::NAME));
                    $left_item = trim($request->get(self::LEFT_ITEM));
                    $buy_price = trim($request->get(self::BUY_PRICE));
                    $category_id = trim($request->get(self::CATEGORY_ID));

                    $isPriceTrack = false;

                    if ($item->buy_price != $buy_price) {
                        $isPriceTrack = true;
                    }

                    $item->code = $code;
                    $item->name = $name;
                    $item->left_item += $left_item;
                    $item->category_id = $category_id;
                    $item->buy_price = $buy_price;
                    $item->shop_id = $shop_id;

                    $item->save();

                    if ($isPriceTrack) {
                        $buy_price_track = new BuyPriceTrack();
                        $buy_price_track->item_id = $item->id;
                        $buy_price_track->buy_price = $buy_price;
                        $buy_price_track->shop_id = $shop_id;

                        $buy_price_track->save();
                    }

                    return jsend_success(new ItemResource($item), JsonResponse::HTTP_CREATED);
                }
            }
            return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (Exception $ex) {
            Log::error(__('api.updated-failed', ['model' => class_basename(Item::class)]), [
                'code' => $ex->getCode(),
                'trace' => $ex->getTrace(),
            ]);

            return jsend_error(__('api.updated-failed', ['model' => class_basename(Item::class)]), [
                $ex->getCode(),
                ErrorType::UPDATE_ERROR,
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Item $item)
    {
        try {
            $user = Auth::user();
            $shops = $user->shops;

            foreach ($shops as $shop) {
                if ($shop->id == $item->shop_id) {
                    $item->delete();

                    return jsend_success(null, JsonResponse::HTTP_NO_CONTENT);
                }
            }
            return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (Exception $ex) {
            return jsend_error(__('api.deleted-failed', ['model' => class_basename(Item::class)]), [
                $ex->getCode(),
                ErrorType::DELETE_ERROR,
            ]);
        }
    }

    public function import()
    {
        Excel::import(new ItemImport, request()->file('file'));

        return jsend_success(['message' => 'Successfully imported!'], JsonResponse::HTTP_ACCEPTED);
    }
}
