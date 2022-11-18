<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BuyCreditRequest;
use App\Models\BuyCredit;
use App\Models\BuyRecord;
use App\Utils\ErrorType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class BuyCreditController extends Controller
{
    const BUY_RECORD_ID = 'buy_record_id';
    const AMOUNT = 'amount';

    public function store(BuyCreditRequest $request)
    {
        try {

            $buy_record_id = trim($request->get(self::BUY_RECORD_ID));
            $amount = trim($request->get(self::AMOUNT));
            
            $buy_record = BuyRecord::find($buy_record_id);
            if($buy_record->credit >= $amount){
                $buy_credit = new BuyCredit();
                $buy_credit->buy_record_id = $buy_record_id;
                $buy_credit->amount = $amount;

                $buy_credit->save();

                $buy_record->credit -= $amount;

                $buy_record->save();

                return jsend_success($buy_credit, JsonResponse::HTTP_CREATED);
            }else{
                return jsend_fail(['message' => 'Amount is greater than credit.'], JsonResponse::HTTP_BAD_REQUEST);
            }

        } catch (Exception $ex) {
            Log::error(__('api.saved-failed', ['model' => class_basename(BuyCredit::class)]), [
                'code' => $ex->getCode(),
                'trace' => $ex->getTrace(),
            ]);

            return jsend_error(__('api.saved-failed', ['model' => class_basename(BuyCredit::class)]), [
                $ex->getCode(),
                ErrorType::SAVE_ERROR,
            ]);
        }
    }

    public function destroy(BuyCredit $buy_credit)
    {
        try {
            $buy_record = BuyRecord::find($buy_credit->buy_record_id);
            $buy_record->credit += $buy_credit->amount;

            $buy_record->save();

            $buy_credit->delete();

            return jsend_success(null, JsonResponse::HTTP_NO_CONTENT);
        } catch (Exception $ex) {
            return jsend_error(__('api.deleted-failed', ['model' => class_basename(BuyCredit::class)]), [
                $ex->getCode(),
                ErrorType::DELETE_ERROR,
            ]);
        }
    }
}
