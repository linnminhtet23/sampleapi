<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BuyRequest;
use App\Http\Resources\BuyResource;
use App\Models\BuyRecord;
use App\Models\Shop;
use App\Models\SingleBuy;
use App\Utils\ErrorType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BuyController extends Controller
{

    public function index(Shop $shop)
    {
        $user = Auth::user();
        $shops = $user->shops;

        $buy_records = [];

        if ($shop->buyRecords->sortByDesc('created_at')->isEmpty() && $shop->id == null) {
            foreach ($shops as $single_shop) {
                foreach ($single_shop->buyRecords->sortByDesc('created_at') as $buy_record) {
                    array_push($buy_records, $buy_record);
                }
            }
        } else {
            foreach ($shops as $single_shop) {
                if ($single_shop->id == $shop->id) {
                    foreach ($shop->buyRecords->sortByDesc('created_at') as $buy_record) {
                        array_push($buy_records, $buy_record);
                    }
                    break;
                }
            }
        }

        return response()->json(["status" => "success", "data" => BuyResource::collection($buy_records), "total" => count($buy_records)]);
    }

    public function store(BuyRequest $request)
    {
        try {

            $merchant_name = $request->get('merchant_name');
            $whole_total = $request->get('whole_total');
            $paid = $request->get('paid');
            $credit = $request->get('credit');
            $shop_id = $request->get('shop_id');
            $single_buys = $request->get('single_buys');

            $user = Auth::user();
            $shops = $user->shops;

            foreach ($shops as $single_shop) {
                if ($single_shop->id == $shop_id) {
                    $buy_record = new BuyRecord();
                    $buy_record->merchant_name = $merchant_name;
                    $buy_record->whole_total = $whole_total;
                    $buy_record->paid = $paid;
                    $buy_record->credit = $credit;
                    $buy_record->shop_id = $shop_id;

                    $buy_record->save();

                    foreach ($single_buys as $single_buy) {
                        $single_buy_model = new SingleBuy();
                        $single_buy_model->buy_record_id = $buy_record->id;
                        $single_buy_model->item_id = $single_buy["item_id"];
                        $single_buy_model->price = $single_buy["price"];
                        $single_buy_model->quantity = $single_buy["quantity"];
                        $single_buy_model->subtotal = $single_buy["subtotal"];

                        $single_buy_model->save();
                    }
                    return jsend_success(new BuyResource($buy_record), JsonResponse::HTTP_CREATED);
                }
            }
        } catch (Exception $ex) {
            Log::error(__('api.saved-failed', ['model' => class_basename(BuyRecord::class)]), [
                'code' => $ex->getCode(),
                'trace' => $ex->getTrace(),
            ]);

            return jsend_error(__('api.saved-failed', ['model' => class_basename(BuyRecord::class)]), [
                $ex->getCode(),
                ErrorType::SAVE_ERROR,
            ]);
        }
    }

    public function show(BuyRecord $buy_record)
    {
        $user = Auth::user();
        $shops = $user->shops;

        foreach ($shops as $shop) {
            if ($shop->id == $buy_record->shop_id) {
                return jsend_success(new BuyResource($buy_record));
            }
        }
        return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
    }

    public function destroy(BuyRecord $buy_record)
    {
        try {
            $user = Auth::user();
            $shops = $user->shops;

            foreach ($shops as $shop) {
                if ($shop->id == $buy_record->shop_id) {
                    $buy_record->delete();

                    return jsend_success(null, JsonResponse::HTTP_NO_CONTENT);
                }
            }
            return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (Exception $ex) {
            return jsend_error(__('api.deleted-failed', ['model' => class_basename(BuyRecord::class)]), [
                $ex->getCode(),
                ErrorType::DELETE_ERROR,
            ]);
        }
    }
}
