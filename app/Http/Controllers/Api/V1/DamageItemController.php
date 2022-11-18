<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DamageItemRequest;
use App\Http\Resources\DamageItemResource;
use App\Models\BuyRecord;
use App\Models\DamageItem;
use App\Models\Shop;
use App\Models\SingleBuy;
use App\Utils\ErrorType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DamageItemController extends Controller
{
    const SINGLE_BUY_ID = 'single_buy_id';
    const QUANTITY = 'quantity';
    const SHOP_ID = 'shop_id';

    public function index(Shop $shop)
    {
        $user = Auth::user();
        $shops = $user->shops;

        $damage_items = [];

        if ($shop->damageItems->sortByDesc('created_at')->isEmpty() && $shop->id == null) {
            foreach ($shops as $single_shop) {
                foreach ($single_shop->damageItems->sortByDesc('created_at') as $damage_item) {
                    array_push($damage_items, $damage_item);
                }
            }
        } else {
            foreach ($shops as $single_shop) {
                if ($single_shop->id == $shop->id) {
                    foreach ($shop->damageItems->sortByDesc('created_at') as $damage_item) {
                        array_push($damage_items, $damage_item);
                    }
                    break;
                }
            }
        }

        return response()->json(["status" => "success", "data" => DamageItemResource::collection($damage_items), "total" => count($damage_items)]);
    }

    public function store(DamageItemRequest $request)
    {
        DB::beginTransaction();
        try {
            $shop_id = $request->get(self::SHOP_ID);

            $user = Auth::user();
            $shops = $user->shops;


            foreach ($shops as $singleShop) {
                if ($singleShop->id == $shop_id) {
                    $single_buy_id = trim($request->get(self::SINGLE_BUY_ID));
                    $quantity = trim($request->get(self::QUANTITY));

                    $single_buy = SingleBuy::find($single_buy_id);
                    if ($quantity > $single_buy->quantity) {
                        return jsend_fail(['error' => 'Damage item quantity is greater than buy quantity.'], JsonResponse::HTTP_BAD_REQUEST);
                    }

                    $single_buy->quantity -= $quantity;
                    $single_buy->subtotal -= $quantity * $single_buy->price;
                    $single_buy->save();

                    $buy_record = BuyRecord::find($single_buy->buy_record_id);
                    $buy_record->whole_total -= $quantity * $single_buy->price;
                    $buy_record->credit = $buy_record->whole_total - $buy_record->paid;
                    $buy_record->save();

                    $damage_item = new DamageItem();
                    $damage_item->single_buy_id = $single_buy_id;
                    $damage_item->quantity = $quantity;
                    $damage_item->shop_id = $shop_id;

                    $damage_item->save();

                    DB::commit();
                    return jsend_success(new DamageItemResource($damage_item), JsonResponse::HTTP_CREATED);
                }
            }
            return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error(__('api.saved-failed', ['model' => class_basename(DamageItem::class)]), [
                'code' => $ex->getCode(),
                'trace' => $ex->getTrace(),
            ]);

            return jsend_error(__('api.saved-failed', ['model' => class_basename(DamageItem::class)]), [
                $ex->getCode(),
                ErrorType::SAVE_ERROR,
            ]);
        }
    }

    public function show(DamageItem $damage_item)
    {
        $user = Auth::user();
        $shops = $user->shops;

        foreach ($shops as $shop) {
            if ($shop->id == $damage_item->shop_id) {
                return jsend_success(new DamageItemResource($damage_item));
            }
        }
        return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
    }

    public function changeStatus(DamageItem $damage_item)
    {
        try {
            $damage_item->status = !$damage_item->status;
            $damage_item->save();

            return jsend_success(['message' => 'Changed Successfully!'], JsonResponse::HTTP_CREATED);
        } catch (Exception $ex) {
            Log::error(__('api.changed-failed', ['model' => class_basename(DamageItem::class)]), [
                'code' => $ex->getCode(),
                'trace' => $ex->getTrace(),
            ]);

            return jsend_error(__('api.changed-failed', ['model' => class_basename(DamageItem::class)]), [
                $ex->getCode(),
                ErrorType::UPDATE_ERROR,
            ]);
        }
    }

    public function destroy(DamageItem $damage_item)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $shops = $user->shops;

            foreach ($shops as $shop) {
                if ($shop->id == $damage_item->shop_id) {
                    $single_buy = SingleBuy::find($damage_item->single_buy_id);
                    $single_buy->quantity += $damage_item->quantity;
                    $single_buy->subtotal += $damage_item->quantity * $single_buy->price;
                    $single_buy->save();

                    $buy_record = BuyRecord::find($single_buy->buy_record_id);
                    $buy_record->whole_total += $damage_item->quantity * $single_buy->price;
                    $buy_record->credit = $buy_record->whole_total - $buy_record->paid;
                    $buy_record->save();

                    $damage_item->delete();

                    DB::commit();
                    return jsend_success(null, JsonResponse::HTTP_NO_CONTENT);
                }
            }
            return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (Exception $ex) {
            DB::rollBack();
            return jsend_error(__('api.deleted-failed', ['model' => class_basename(DamageItem::class)]), [
                $ex->getCode(),
                ErrorType::DELETE_ERROR,
            ]);
        }
    }
}
