<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Utils\ErrorType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\SaleRecord;

class ProfitController extends Controller
{
    public function profit(int $shop_id)
    {
        $user = Auth::user();
        $shops = $user->shops;

        foreach ($shops as $single_shop) {
            if ($single_shop->id == $shop_id) {
                if (request()->has('start_date') && request()->has('end_date')) {
                    $start_date = request()->input('start_date');
                    $end_date = request()->input('end_date');

                    $daily = SaleRecord::select(
                        DB::raw('Sum(purchase_total) as purchase_total'),
                        DB::raw('Sum(sale_record_total) as sale_record_total'),
                        DB::raw('Sum(extra_charges) as extra_charges'),
                        DB::raw('Sum(whole_total) as whole_total'),
                        DB::raw('Sum(credit) as credit'),
                        DB::raw('Sum(paid) as paid'),
                    )->where('shop_id', $shop_id)->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59'])->first();

                    $expense = Expense::select(
                        DB::raw('Sum(amount) as amount'),
                    )->where('shop_id', $shop_id)->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59'])->first();
                } else {
                    $daily = SaleRecord::select(
                        DB::raw('Sum(purchase_total) as purchase_total'),
                        DB::raw('Sum(sale_record_total) as sale_record_total'),
                        DB::raw('Sum(extra_charges) as extra_charges'),
                        DB::raw('Sum(whole_total) as whole_total'),
                        DB::raw('Sum(credit) as credit'),
                        DB::raw('Sum(paid) as paid'),
                    )->where('shop_id', $shop_id)->first();

                    $expense = Expense::select(
                        DB::raw('Sum(amount) as amount'),
                    )->where('shop_id', $shop_id)->first();
                }


                return response()->json(["status" => "success", "daily" => $daily, "expense" => $expense]);
            }
        }
        return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
    }
}
