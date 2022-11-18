<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaleRequest;
use App\Http\Resources\SaleResource;
use App\Models\Item;
use App\Models\SaleRecord;
use App\Models\Shop;
use App\Models\SingleSale;
use App\Utils\ErrorType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{

    public function index(Shop $shop)
    {
        $user = Auth::user();
        $shops = $user->shops;

        $sale_records = [];

        if ($shop->saleRecords->sortByDesc('created_at')->isEmpty() && $shop->id == null) {
            foreach ($shops as $single_shop) {
                foreach ($single_shop->saleRecords->sortByDesc('created_at') as $sale_record) {
                    array_push($sale_records, $sale_record);
                }
            }
        } else {
            foreach ($shops as $single_shop) {
                if ($single_shop->id == $shop->id) {
                    foreach ($shop->saleRecords->sortByDesc('created_at') as $sale_record) {
                        array_push($sale_records, $sale_record);
                    }
                    break;
                }
            }
        }

        return response()->json(["status" => "success", "data" => SaleResource::collection($sale_records), "total" => count($sale_records)]);
    }

    public function store(SaleRequest $request)
    {
        try {

            $customer_name = $request->get('customer_name');
            $purchase_total = $request->get('purchase_total');
            $sale_record_total = $request->get('sale_record_total');
            $extra_charges = $request->get('extra_charges');
            $whole_total = $request->get('whole_total');
            $paid = $request->get('paid');
            $credit = $request->get('credit');
            $shop_id = $request->get('shop_id');
            $single_sales = $request->get('single_sales');

            $user = Auth::user();
            $shops = $user->shops;

            foreach ($shops as $single_shop) {
                if ($single_shop->id == $shop_id) {
                    $sale_record = new SaleRecord();
                    $sale_record->customer_name = $customer_name;
                    $sale_record->purchase_total = $purchase_total;
                    $sale_record->sale_record_total = $sale_record_total;
                    $sale_record->extra_charges = $extra_charges;
                    $sale_record->whole_total = $whole_total;
                    $sale_record->paid = $paid;
                    $sale_record->credit = $credit;
                    $sale_record->shop_id = $shop_id;

                    $sale_record->save();

                    foreach ($single_sales as $single_sale) {
                        $item = Item::find($single_sale["item_id"]);

                        $single_sale_model = new SingleSale();
                        $single_sale_model->sale_record_id = $sale_record->id;
                        $single_sale_model->item_id = $single_sale["item_id"];
                        $single_sale_model->price = $single_sale["price"];
                        $single_sale_model->quantity = $single_sale["quantity"];
                        $single_sale_model->subtotal = $single_sale["subtotal"];

                        $single_sale_model->save();

                        $item->left_item -= $single_sale["quantity"];

                        $item->save();
                    }
                    return jsend_success(new SaleResource($sale_record), JsonResponse::HTTP_CREATED);
                }
            }
        } catch (Exception $ex) {
            Log::error(__('api.saved-failed', ['model' => class_basename(SaleRecord::class)]), [
                'code' => $ex->getCode(),
                'trace' => $ex->getTrace(),
            ]);

            return jsend_error(__('api.saved-failed', ['model' => class_basename(SaleRecord::class)]), [
                $ex->getCode(),
                ErrorType::SAVE_ERROR,
            ]);
        }
    }

    public function show(SaleRecord $sale_record)
    {
        $user = Auth::user();
        $shops = $user->shops;

        foreach ($shops as $shop) {
            if ($shop->id == $sale_record->shop_id) {
                return jsend_success(new SaleResource($sale_record));
            }
        }
        return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
    }

    public function destroy(SaleRecord $sale_record)
    {
        try {
            $user = Auth::user();
            $shops = $user->shops;

            foreach ($shops as $shop) {
                if ($shop->id == $sale_record->shop_id) {
                    $sale_record->delete();

                    return jsend_success(null, JsonResponse::HTTP_NO_CONTENT);
                }
            }
            return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (Exception $ex) {
            return jsend_error(__('api.deleted-failed', ['model' => class_basename(SaleRecord::class)]), [
                $ex->getCode(),
                ErrorType::DELETE_ERROR,
            ]);
        }
    }

    public function daily(int $shop_id)
    {
        $user = Auth::user();
        $shops = $user->shops;

        foreach ($shops as $single_shop) {
            if ($single_shop->id == $shop_id) {
                if (request()->has('start_date') && request()->has('end_date')) {
                    $start_date = request()->input('start_date');
                    $end_date = request()->input('end_date');

                    $daily = SaleRecord::select(
                        DB::raw('Date(created_at) as day'),
                        DB::raw('Sum(purchase_total) as purchase_total'),
                        DB::raw('Sum(sale_record_total) as sale_record_total'),
                        DB::raw('Sum(extra_charges) as extra_charges'),
                        DB::raw('Sum(whole_total) as whole_total'),
                        DB::raw('Sum(credit) as credit'),
                        DB::raw('Sum(paid) as paid'),
                    )->groupBy('day')
                        ->orderBy('day', 'desc')
                        ->where('shop_id', $shop_id)
                        ->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59'])
                        ->get();
                } else {
                    $daily = SaleRecord::select(
                        DB::raw('Date(created_at) as day'),
                        DB::raw('Sum(purchase_total) as purchase_total'),
                        DB::raw('Sum(sale_record_total) as sale_record_total'),
                        DB::raw('Sum(extra_charges) as extra_charges'),
                        DB::raw('Sum(whole_total) as whole_total'),
                        DB::raw('Sum(credit) as credit'),
                        DB::raw('Sum(paid) as paid'),
                    )->groupBy('day')
                        ->orderBy('day', 'desc')
                        ->where('shop_id', $shop_id)
                        ->get();
                }


                return response()->json(["status" => "success", "data" => $daily, "total" => count($daily)]);
            }
        }
        return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
    }

    public function monthly(int $shop_id)
    {
        $user = Auth::user();
        $shops = $user->shops;

        foreach ($shops as $single_shop) {
            if ($single_shop->id == $shop_id) {
                $daily = SaleRecord::select(
                    DB::raw('Month(created_at) as month'),
                    DB::raw('Year(created_at) as year'),
                    DB::raw('Sum(purchase_total) as purchase_total'),
                    DB::raw('Sum(sale_record_total) as sale_record_total'),
                    DB::raw('Sum(extra_charges) as extra_charges'),
                    DB::raw('Sum(whole_total) as whole_total'),
                    DB::raw('Sum(credit) as credit'),
                    DB::raw('Sum(paid) as paid'),
                )->groupBy('month', 'year')
                    ->orderBy('year', 'desc')
                    ->where('shop_id', $shop_id)
                    ->get();

                return response()->json(["status" => "success", "data" => $daily, "total" => count($daily)]);
            }
        }
        return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
    }

    public function yearly(int $shop_id)
    {
        $user = Auth::user();
        $shops = $user->shops;

        foreach ($shops as $single_shop) {
            if ($single_shop->id == $shop_id) {
                $daily = SaleRecord::select(
                    DB::raw('Year(created_at) as year'),
                    DB::raw('Sum(purchase_total) as purchase_total'),
                    DB::raw('Sum(sale_record_total) as sale_record_total'),
                    DB::raw('Sum(extra_charges) as extra_charges'),
                    DB::raw('Sum(whole_total) as whole_total'),
                    DB::raw('Sum(credit) as credit'),
                    DB::raw('Sum(paid) as paid'),
                )->groupBy('year')
                    ->orderBy('year', 'desc')
                    ->where('shop_id', $shop_id)
                    ->get();

                return response()->json(["status" => "success", "data" => $daily, "total" => count($daily)]);
            }
        }
        return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
    }

    public function moreSaleItems(int $shop_id)
    {
        $user = Auth::user();
        $shops = $user->shops;

        foreach ($shops as $single_shop) {
            if ($single_shop->id == $shop_id) {
                $more_sales = SingleSale::join('sale_records', 'single_sales.sale_record_id', '=', 'sale_records.id')
                    ->select(
                        DB::raw('item_id as item_id'),
                        DB::raw('Sum(quantity) as total'),
                    )->groupBy('item_id')->where('sale_records.shop_id', '=', $shop_id)->with('item')->get();

                return response()->json(["status" => "success", "data" => $more_sales]);
            }
        }
        return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
    }
}
