<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Models\Shop;
use App\Utils\ErrorType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExpenseController extends Controller
{
    const NAME = 'name';
    const AMOUNT = 'amount';
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

        $expenses = [];

        if (request()->has('start_date') && request()->has('end_date')) {
            $start_date = request()->input('start_date');
            $end_date = request()->input('end_date');

            if ($shop->expenses->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59'])->isEmpty() && $shop->id == null) {
                foreach ($shops as $single_shop) {
                    foreach ($single_shop->expenses->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']) as $expense) {
                        array_push($expenses, $expense);
                    }
                }
            } else {
                foreach ($shops as $single_shop) {
                    if ($single_shop->id == $shop->id) {
                        foreach ($shop->expenses->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']) as $expense) {
                            array_push($expenses, $expense);
                        }
                        break;
                    }
                }
            }
        } else {
            if ($shop->expenses->isEmpty() && $shop->id == null) {
                foreach ($shops as $single_shop) {
                    foreach ($single_shop->expenses as $expense) {
                        array_push($expenses, $expense);
                    }
                }
            } else {
                foreach ($shops as $single_shop) {
                    if ($single_shop->id == $shop->id) {
                        foreach ($shop->expenses as $expense) {
                            array_push($expenses, $expense);
                        }
                        break;
                    }
                }
            }
        }

        return response()->json(["status" => "success", "data" => ExpenseResource::collection($expenses), "total" => count($expenses)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ExpenseRequest $request)
    {
        try {
            $shop_id = $request->get(self::SHOP_ID);

            $user = Auth::user();
            $shops = $user->shops;


            foreach ($shops as $singleShop) {
                if ($singleShop->id == $shop_id) {
                    $name = trim($request->get(self::NAME));
                    $amount = trim($request->get(self::AMOUNT));

                    $expense = new Expense();
                    $expense->name = $name;
                    $expense->amount = $amount;
                    $expense->shop_id = $shop_id;

                    $expense->save();

                    return jsend_success(new ExpenseResource($expense), JsonResponse::HTTP_CREATED);
                }
            }
            return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (Exception $ex) {
            Log::error(__('api.saved-failed', ['model' => class_basename(Expense::class)]), [
                'code' => $ex->getCode(),
                'trace' => $ex->getTrace(),
            ]);

            return jsend_error(__('api.saved-failed', ['model' => class_basename(Expense::class)]), [
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
    public function show(Expense $expense)
    {
        $user = Auth::user();
        $shops = $user->shops;

        foreach ($shops as $shop) {
            if ($shop->id == $expense->shop_id) {
                return jsend_success(new ExpenseResource($expense));
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
    public function update(ExpenseRequest $request, Expense $expense)
    {
        try {
            $shop_id = $request->get(self::SHOP_ID);

            $user = Auth::user();
            $shops = $user->shops;


            foreach ($shops as $singleShop) {
                if ($singleShop->id == $shop_id) {
                    $name = trim($request->get(self::NAME));
                    $amount = trim($request->get(self::AMOUNT));

                    $expense->name = $name;
                    $expense->amount = $amount;
                    $expense->shop_id = $shop_id;

                    $expense->save();

                    return jsend_success(new ExpenseResource($expense), JsonResponse::HTTP_CREATED);
                }
            }
            return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (Exception $ex) {
            Log::error(__('api.updated-failed', ['model' => class_basename(Expense::class)]), [
                'code' => $ex->getCode(),
                'trace' => $ex->getTrace(),
            ]);

            return jsend_error(__('api.updated-failed', ['model' => class_basename(Expense::class)]), [
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
    public function destroy(Expense $expense)
    {
        try {
            $user = Auth::user();
            $shops = $user->shops;

            foreach ($shops as $shop) {
                if ($shop->id == $expense->shop_id) {
                    $expense->delete();

                    return jsend_success(null, JsonResponse::HTTP_NO_CONTENT);
                }
            }
            return jsend_fail(['error' => 'Unauthorized.'], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (Exception $ex) {
            return jsend_error(__('api.deleted-failed', ['model' => class_basename(Expense::class)]), [
                $ex->getCode(),
                ErrorType::DELETE_ERROR,
            ]);
        }
    }
}
