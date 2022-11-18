<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreditRequest;
use App\Models\Credit;
use App\Models\SaleRecord;
use App\Utils\ErrorType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CreditController extends Controller
{
    const SALE_RECORD_ID = 'sale_record_id';
    const AMOUNT = 'amount';

    public function store(CreditRequest $request)
    {
        try {

            $sale_record_id = trim($request->get(self::SALE_RECORD_ID));
            $amount = trim($request->get(self::AMOUNT));

            $sale_record = SaleRecord::find($sale_record_id);

            if ($sale_record->credit >= $amount) {
                $credit = new Credit();
                $credit->sale_record_id = $sale_record_id;
                $credit->amount = $amount;

                $credit->save();

                $sale_record->credit -= $amount;

                $sale_record->save();

                return jsend_success($credit, JsonResponse::HTTP_CREATED);
            } else {
                return jsend_fail(['message' => 'Amount is greater than credit.'], JsonResponse::HTTP_BAD_REQUEST);
            }
        } catch (Exception $ex) {
            Log::error(__('api.saved-failed', ['model' => class_basename(Credit::class)]), [
                'code' => $ex->getCode(),
                'trace' => $ex->getTrace(),
            ]);

            return jsend_error(__('api.saved-failed', ['model' => class_basename(Credit::class)]), [
                $ex->getCode(),
                ErrorType::SAVE_ERROR,
            ]);
        }
    }

    public function destroy(Credit $credit)
    {
        try {
            $sale_record = SaleRecord::find($credit->sale_record_id);
            $sale_record->credit += $credit->amount;

            $sale_record->save();

            $credit->delete();

            return jsend_success(null, JsonResponse::HTTP_NO_CONTENT);
        } catch (Exception $ex) {
            return jsend_error(__('api.deleted-failed', ['model' => class_basename(Credit::class)]), [
                $ex->getCode(),
                ErrorType::DELETE_ERROR,
            ]);
        }
    }
}
